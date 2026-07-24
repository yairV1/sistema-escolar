<?php

namespace App\Modules\Calendario\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Requests\EventoMoverRequest;
use App\Modules\Calendario\Requests\EventoRequest;
use App\Modules\Calendario\Services\EventoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventoController extends Controller
{
    public function __construct(private EventoService $eventoService) {}

    public function store(EventoRequest $request): JsonResponse
    {
        $evento = $this->eventoService->crear($request->validated(), $request->user());

        return response()->json(['success' => true, 'message' => 'Evento creado correctamente.', 'id' => $evento->id_evento]);
    }

    public function show(Request $request, Evento $evento): JsonResponse
    {
        $usuario = $request->user();
        abort_unless($usuario->can('view', $evento), 403);

        $evento->load([
            'categoria', 'creador', 'curso',
            'participantes.usuario', 'adjuntos.usuarioSubio', 'comentarios.usuario',
            'historial' => fn ($q) => $q->limit(10),
        ]);

        $datos = $evento->only([
            'id_evento', 'id_categoria', 'titulo', 'descripcion',
            'hora_inicio', 'hora_fin', 'todo_el_dia',
            'color_override', 'prioridad', 'estado', 'visibilidad',
            'id_curso', 'id_asignacion', 'salon', 'ubicacion',
            'tipo_recurrencia', 'intervalo_recurrencia', 'dias_semana_recurrencia',
            'recordatorio_minutos_antes',
        ]);
        // Los casts 'date' serializan a ISO 8601 completo por defecto — los
        // inputs <input type="date"> del modal necesitan exactamente Y-m-d.
        $datos['fecha_inicio'] = $evento->fecha_inicio->toDateString();
        $datos['fecha_fin'] = $evento->fecha_fin->toDateString();
        $datos['fecha_fin_recurrencia'] = $evento->fecha_fin_recurrencia?->toDateString();
        $datos['categoria_nombre'] = $evento->categoria?->nombre;
        $datos['creador_nombre'] = trim($evento->creador->nombres.' '.$evento->creador->apellidos);

        return response()->json([
            'success' => true,
            'evento' => $datos,
            'puede_editar' => $usuario->can('update', $evento),
            'puede_eliminar' => $usuario->can('delete', $evento),
            'participantes' => $evento->participantes->map(fn ($p) => [
                'id_usuario' => $p->id_usuario,
                'nombre' => trim($p->usuario->nombres.' '.$p->usuario->apellidos),
                'rol_participacion' => $p->rol_participacion,
            ]),
            'adjuntos' => $evento->adjuntos->map(fn ($a) => [
                'id_adjunto' => $a->id_adjunto,
                'nombre_original' => $a->nombre_original,
                'url' => Storage::disk('public')->url($a->ruta),
                'tamano_bytes' => $a->tamano_bytes,
            ]),
            'comentarios' => $evento->comentarios->map(fn ($c) => [
                'id_comentario' => $c->id_comentario,
                'comentario' => $c->comentario,
                'autor' => trim($c->usuario->nombres.' '.$c->usuario->apellidos),
                'es_propio' => $c->id_usuario === $usuario->id_usuario,
                'fecha' => $c->created_at->format('d/m/Y H:i'),
            ]),
            'historial' => $evento->historial->map(fn ($h) => [
                'accion' => $h->accion,
                'fecha' => $h->created_at->format('d/m/Y H:i'),
            ]),
        ]);
    }

    public function update(EventoRequest $request, Evento $evento): JsonResponse
    {
        $this->eventoService->actualizar($evento, $request->validated(), $request->user());

        return response()->json(['success' => true, 'message' => 'Evento actualizado correctamente.']);
    }

    public function mover(EventoMoverRequest $request, Evento $evento): JsonResponse
    {
        $this->eventoService->mover($evento, $request->input('fecha_ocurrencia'), $request->nuevaPosicion(), $request->user());

        return response()->json(['success' => true, 'message' => 'Evento movido correctamente.']);
    }

    public function duplicar(Request $request, Evento $evento): JsonResponse
    {
        abort_unless($request->user()->can('update', $evento), 403);

        $copia = $this->eventoService->duplicar($evento, $request->user());

        return response()->json(['success' => true, 'message' => 'Evento duplicado correctamente.', 'id' => $copia->id_evento]);
    }

    public function cambiarEstado(Request $request, Evento $evento): JsonResponse
    {
        abort_unless($request->user()->can('update', $evento), 403);

        $nuevoEstado = $request->string('estado')->value();
        if (! in_array($nuevoEstado, ['pendiente', 'completado', 'cancelado'], true)) {
            return response()->json(['success' => false, 'message' => 'Estado no válido.'], 422);
        }

        $this->eventoService->cambiarEstado($evento, $nuevoEstado, $request->user());

        return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente.']);
    }

    public function desactivar(Request $request, Evento $evento): JsonResponse
    {
        abort_unless($request->user()->can('delete', $evento), 403);

        $this->eventoService->desactivar($evento, $request->user());

        return response()->json(['success' => true, 'message' => 'Evento desactivado correctamente.']);
    }

    public function activar(Request $request, Evento $evento): JsonResponse
    {
        abort_unless($request->user()->can('delete', $evento), 403);

        $this->eventoService->activar($evento, $request->user());

        return response()->json(['success' => true, 'message' => 'Evento reactivado correctamente.']);
    }
}
