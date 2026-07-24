<?php

namespace App\Modules\Calendario\Services\Occurrences;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Contracts\CalendarioOccurrenceSource;
use App\Modules\Calendario\DTO\CalendarioItem;
use App\Modules\Calendario\DTO\OcurrenciaExpandida;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Services\RecurrenceExpansionService;
use App\Modules\Calendario\Services\VisibilidadCalendarioService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;

/**
 * Eventos reales (tabla `eventos`) creados vía CRUD. Expande cada plantilla
 * recurrente en sus ocurrencias dentro del rango pedido vía
 * RecurrenceExpansionService (que ya aplica evento_excepciones) — nunca
 * duplica filas por ocurrencia.
 *
 * Visibilidad: 'publico' (respetando categoria.roles_ver + curso visible),
 * 'compartido' (el usuario es un evento_participantes de esa fila), o el
 * propio creador.
 */
class EventoOccurrenceSource implements CalendarioOccurrenceSource
{
    public function __construct(
        private VisibilidadCalendarioService $visibilidad,
        private RecurrenceExpansionService $recurrencia,
    ) {}

    public function occurrencesBetween(
        CarbonImmutable $desde,
        CarbonImmutable $hasta,
        Usuario $usuario,
        array $filtros
    ): array {
        $cursosVisibles = $this->visibilidad->cursosVisibles($usuario);

        $eventos = Evento::query()
            ->activos()
            ->where('fecha_inicio', '<=', $hasta->toDateString())
            ->where(function ($query) use ($desde) {
                $query->where(function ($q) use ($desde) {
                    $q->where('tipo_recurrencia', 'ninguna')->where('fecha_fin', '>=', $desde->toDateString());
                })->orWhere(function ($q) use ($desde) {
                    $q->where('tipo_recurrencia', '!=', 'ninguna')
                        ->where(function ($q2) use ($desde) {
                            $q2->whereNull('fecha_fin_recurrencia')->orWhere('fecha_fin_recurrencia', '>=', $desde->toDateString());
                        });
                });
            })
            ->where(function ($query) use ($usuario, $cursosVisibles) {
                $query->where('id_usuario_creador', $usuario->id_usuario)
                    ->orWhere(function ($sub) use ($usuario) {
                        $sub->where('visibilidad', 'compartido')
                            ->whereHas('participantes', fn ($q) => $q->where('id_usuario', $usuario->id_usuario));
                    })
                    ->orWhere(function ($sub) use ($cursosVisibles) {
                        $sub->where('visibilidad', 'publico')
                            ->whereHas('categoria', fn ($q) => $q->activas())
                            ->when(is_array($cursosVisibles), fn ($q) => $q->where(
                                fn ($q2) => $q2->whereNull('id_curso')->orWhereIn('id_curso', $cursosVisibles)
                            ));
                    });
            })
            ->when(! empty($filtros['categorias']), fn ($q) => $q->whereIn('id_categoria', $filtros['categorias']))
            ->when(! empty($filtros['curso']), fn ($q) => $q->where('id_curso', $filtros['curso']))
            ->with(['categoria', 'curso', 'excepciones'])
            ->get()
            ->filter(fn (Evento $evento) => $evento->categoria && $this->visibilidad->puedeVerCategoria($usuario, $evento->categoria));

        $items = [];
        foreach ($eventos as $evento) {
            $items = array_merge($items, $this->itemsDeEvento($evento, $desde, $hasta, $usuario));
        }

        return $items;
    }

    /** @return CalendarioItem[] */
    private function itemsDeEvento(Evento $evento, CarbonImmutable $desde, CarbonImmutable $hasta, Usuario $usuario): array
    {
        $ocurrencias = $this->recurrencia->expandir($evento, $desde, $hasta);
        if (empty($ocurrencias)) {
            return [];
        }

        $editable = Gate::forUser($usuario)->allows('update', $evento);
        $esRecurrente = $evento->tipo_recurrencia !== 'ninguna';

        return array_map(function (OcurrenciaExpandida $ocurrencia) use ($evento, $esRecurrente, $editable) {
            $inicio = $ocurrencia->fechaInicio->toDateString().($evento->todo_el_dia || ! $ocurrencia->horaInicio ? '' : 'T'.$ocurrencia->horaInicio);
            $fin = $evento->todo_el_dia || ! $ocurrencia->horaFin
                ? null
                : $ocurrencia->fechaFin->toDateString().'T'.$ocurrencia->horaFin;

            return new CalendarioItem(
                id: $esRecurrente ? "evento:{$evento->id_evento}:{$ocurrencia->fechaOriginal->toDateString()}" : "evento:{$evento->id_evento}",
                title: $evento->titulo,
                start: $inicio,
                end: $fin,
                allDay: $evento->todo_el_dia,
                color: $evento->color_override ?? $evento->categoria->color,
                tipo: 'evento',
                editable: $editable,
                extendedProps: [
                    'id_evento' => $evento->id_evento,
                    'categoria_id' => $evento->categoria->id_categoria,
                    'categoria_nombre' => $evento->categoria->nombre,
                    'icono' => $evento->categoria->icono,
                    'curso' => $evento->curso?->nombre_curso,
                    'salon' => $evento->salon,
                    'ubicacion' => $evento->ubicacion,
                    'prioridad' => $evento->prioridad,
                    'estado' => $evento->estado,
                    'visibilidad' => $evento->visibilidad,
                    'recurrente' => $esRecurrente,
                    'movida' => $ocurrencia->movida,
                    'fecha_original' => $ocurrencia->fechaOriginal->toDateString(),
                ],
            );
        }, $ocurrencias);
    }
}
