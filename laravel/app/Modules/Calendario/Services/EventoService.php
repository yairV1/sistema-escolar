<?php

namespace App\Modules\Calendario\Services;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Events\EventoActualizado;
use App\Modules\Calendario\Events\EventoCreado;
use App\Modules\Calendario\Events\EventoEliminado;
use App\Modules\Calendario\Events\EventoMovido;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Models\EventoAdjunto;
use App\Modules\Calendario\Models\EventoComentario;
use App\Modules\Calendario\Models\EventoExcepcion;
use App\Modules\Calendario\Models\EventoHistorial;
use App\Modules\Calendario\Models\EventoParticipante;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Toda mutación de Evento (y de lo que le pertenece: participantes,
 * adjuntos, comentarios) pasa por acá: transacción + registro en
 * evento_historial en el mismo sitio donde ocurre la escritura (no vía
 * Observer, para que ambas cosas se vean juntas al leer el código).
 * Comentarios/adjuntos no generan entrada de historial (su propio listado
 * con autor y fecha ya cumple ese rol; el enum de `accion` ni los incluye).
 * Cada método dispara además el evento de dominio (Fase 4) correspondiente
 * — ShouldDispatchAfterCommit se encarga de que solo llegue si la
 * transacción de este método hace commit.
 */
class EventoService
{
    public function crear(array $datos, Usuario $usuario): Evento
    {
        return DB::transaction(function () use ($datos, $usuario) {
            $participantes = Arr::pull($datos, 'participantes', []);

            $evento = Evento::create($datos + [
                'id_usuario_creador' => $usuario->id_usuario,
                'estado' => 'pendiente',
                'estado_activo' => 'activo',
            ]);

            $this->sincronizarParticipantes($evento, $evento->visibilidad === 'compartido' ? $participantes : []);
            $this->registrarHistorial($evento, $usuario, 'creado');
            event(new EventoCreado($evento));

            return $evento;
        });
    }

    public function actualizar(Evento $evento, array $datos, Usuario $usuario): Evento
    {
        return DB::transaction(function () use ($evento, $datos, $usuario) {
            // Debe capturarse ANTES de update(): Eloquent llama a
            // syncOriginal() al terminar de guardar, así que getOriginal()
            // ya reflejaría los valores nuevos si se llamara después.
            $antes = $evento->getOriginal();
            $participantes = Arr::pull($datos, 'participantes', []);
            $eraRecurrente = $evento->tipo_recurrencia !== 'ninguna';

            $evento->update($datos);

            // Si deja de ser recurrente, sus excepciones quedan huérfanas
            // (nunca se consultan para un evento no-recurrente) — se limpian
            // para no dejar basura de datos.
            if ($eraRecurrente && $evento->tipo_recurrencia === 'ninguna') {
                $evento->excepciones()->delete();
            }

            $this->sincronizarParticipantes($evento, $evento->visibilidad === 'compartido' ? $participantes : []);

            $cambios = $this->diffCambios($evento, $antes);
            if ($cambios !== []) {
                $this->registrarHistorial($evento, $usuario, 'actualizado', $cambios);
                event(new EventoActualizado($evento));
            }

            return $evento;
        });
    }

    public function duplicar(Evento $evento, Usuario $usuario): Evento
    {
        return DB::transaction(function () use ($evento, $usuario) {
            $copia = $evento->replicate(['id_evento', 'created_at', 'updated_at']);
            $copia->id_usuario_creador = $usuario->id_usuario;
            $copia->estado = 'pendiente';
            $copia->estado_activo = 'activo';
            $copia->save();

            if ($evento->visibilidad === 'compartido') {
                $this->sincronizarParticipantes($copia, $evento->participantes()->pluck('id_usuario')->all());
            }

            $this->registrarHistorial($copia, $usuario, 'creado');
            event(new EventoCreado($copia));

            return $copia;
        });
    }

    public function cambiarEstado(Evento $evento, string $nuevoEstado, Usuario $usuario): Evento
    {
        return DB::transaction(function () use ($evento, $nuevoEstado, $usuario) {
            $anterior = $evento->estado;
            $evento->update(['estado' => $nuevoEstado]);

            $this->registrarHistorial($evento, $usuario, 'estado_cambiado', ['estado' => [$anterior, $nuevoEstado]]);
            event(new EventoActualizado($evento));

            return $evento;
        });
    }

    public function desactivar(Evento $evento, Usuario $usuario): Evento
    {
        return DB::transaction(function () use ($evento, $usuario) {
            $evento->update(['estado_activo' => 'inactivo']);
            $this->registrarHistorial($evento, $usuario, 'desactivado');
            event(new EventoEliminado($evento));

            return $evento;
        });
    }

    public function activar(Evento $evento, Usuario $usuario): Evento
    {
        return DB::transaction(function () use ($evento, $usuario) {
            $evento->update(['estado_activo' => 'activo']);
            $this->registrarHistorial($evento, $usuario, 'activado');
            event(new EventoCreado($evento));

            return $evento;
        });
    }

    /**
     * Mueve un evento (drag&drop) o, si es recurrente, crea/actualiza la
     * excepción de esa ocurrencia puntual — nunca toca la plantilla de un
     * evento recurrente (ver RecurrenceExpansionService).
     */
    public function mover(Evento $evento, ?string $fechaOcurrencia, array $nuevaPosicion, Usuario $usuario): Evento
    {
        return DB::transaction(function () use ($evento, $fechaOcurrencia, $nuevaPosicion, $usuario) {
            if ($evento->tipo_recurrencia === 'ninguna' || ! $fechaOcurrencia) {
                $evento->update($nuevaPosicion);
            } else {
                EventoExcepcion::updateOrCreate(
                    ['id_evento' => $evento->id_evento, 'fecha_original' => $fechaOcurrencia],
                    [
                        'tipo' => 'movida',
                        'nueva_fecha_inicio' => $nuevaPosicion['fecha_inicio'],
                        'nueva_hora_inicio' => $nuevaPosicion['hora_inicio'] ?? null,
                        'nueva_fecha_fin' => $nuevaPosicion['fecha_fin'],
                        'nueva_hora_fin' => $nuevaPosicion['hora_fin'] ?? null,
                    ]
                );
            }

            $this->registrarHistorial($evento, $usuario, 'movido', [
                'fecha_ocurrencia' => $fechaOcurrencia,
                'nueva_posicion' => $nuevaPosicion,
            ]);
            event(new EventoMovido($evento));

            return $evento;
        });
    }

    public function agregarComentario(Evento $evento, string $texto, Usuario $usuario): EventoComentario
    {
        return EventoComentario::create([
            'id_evento' => $evento->id_evento,
            'id_usuario' => $usuario->id_usuario,
            'comentario' => $texto,
        ]);
    }

    public function eliminarComentario(EventoComentario $comentario): void
    {
        $comentario->delete();
    }

    public function adjuntar(Evento $evento, UploadedFile $archivo, Usuario $usuario): EventoAdjunto
    {
        $ruta = $archivo->store("eventos/{$evento->id_evento}", 'public');

        return EventoAdjunto::create([
            'id_evento' => $evento->id_evento,
            'id_usuario_subio' => $usuario->id_usuario,
            'nombre_original' => $archivo->getClientOriginalName(),
            'ruta' => $ruta,
            'mime_type' => $archivo->getClientMimeType(),
            'tamano_bytes' => $archivo->getSize(),
        ]);
    }

    public function quitarAdjunto(EventoAdjunto $adjunto): void
    {
        Storage::disk('public')->delete($adjunto->ruta);
        $adjunto->delete();
    }

    /** Reemplazo completo (no hay UI de "responder" todavía, así que no hay respuesta previa que preservar). */
    private function sincronizarParticipantes(Evento $evento, array $idsUsuarios): void
    {
        $evento->participantes()->delete();

        $idsUnicos = array_unique(array_filter($idsUsuarios));
        if (! in_array($evento->id_usuario_creador, $idsUnicos, true)) {
            $idsUnicos[] = $evento->id_usuario_creador;
        }

        foreach ($idsUnicos as $idUsuario) {
            EventoParticipante::create([
                'id_evento' => $evento->id_evento,
                'id_usuario' => $idUsuario,
                'rol_participacion' => $idUsuario === $evento->id_usuario_creador ? 'organizador' : 'invitado',
            ]);
        }
    }

    private function diffCambios(Evento $evento, array $antes): array
    {
        $cambios = [];
        foreach ($evento->getChanges() as $campo => $nuevo) {
            if ($campo === 'updated_at') {
                continue;
            }
            $cambios[$campo] = [$antes[$campo] ?? null, $nuevo];
        }

        return $cambios;
    }

    private function registrarHistorial(Evento $evento, Usuario $usuario, string $accion, ?array $cambios = null): void
    {
        EventoHistorial::create([
            'id_evento' => $evento->id_evento,
            'id_usuario' => $usuario->id_usuario,
            'accion' => $accion,
            'cambios' => $cambios,
        ]);
    }
}
