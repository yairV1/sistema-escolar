<?php

namespace App\Modules\Calendario\Services\Occurrences;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Contracts\CalendarioOccurrenceSource;
use App\Modules\Calendario\DTO\CalendarioItem;
use App\Modules\Calendario\Models\EventoCategoria;
use App\Modules\Calendario\Services\VisibilidadCalendarioService;
use App\Modules\Calificaciones\Models\Actividad;
use Carbon\CarbonImmutable;

/**
 * Deriva ocurrencias de todo el día a partir de `actividades.fecha_entrega`
 * (actividades evaluativas ya registradas en Calificaciones). No duplica
 * la actividad: solo la proyecta como marcador en el calendario.
 */
class ActividadOccurrenceSource implements CalendarioOccurrenceSource
{
    public function __construct(private VisibilidadCalendarioService $visibilidad) {}

    public function occurrencesBetween(
        CarbonImmutable $desde,
        CarbonImmutable $hasta,
        Usuario $usuario,
        array $filtros
    ): array {
        $categoria = EventoCategoria::where('slug', 'actividades')->first();
        if (! $categoria || ! $this->visibilidad->puedeVerCategoria($usuario, $categoria)) {
            return [];
        }

        if (! empty($filtros['categorias']) && ! in_array($categoria->id_categoria, $filtros['categorias'], true)) {
            return [];
        }

        $cursosVisibles = $this->visibilidad->cursosVisibles($usuario);
        if (is_array($cursosVisibles) && $cursosVisibles === []) {
            return [];
        }

        $actividades = Actividad::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha_entrega', [$desde->toDateString(), $hasta->toDateString()])
            ->whereHas('asignacion', function ($query) use ($cursosVisibles, $filtros) {
                $query->where('estado', 'activo');
                if (is_array($cursosVisibles)) {
                    $query->whereIn('id_curso', $cursosVisibles);
                }
                if (! empty($filtros['curso'])) {
                    $query->where('id_curso', $filtros['curso']);
                }
            })
            ->with(['asignacion.materia', 'asignacion.curso', 'tipo'])
            ->get();

        return $actividades->map(function (Actividad $actividad) use ($categoria) {
            $asignacion = $actividad->asignacion;

            return new CalendarioItem(
                id: "actividad:{$actividad->id_actividad}",
                title: $actividad->titulo,
                start: $actividad->fecha_entrega,
                end: null,
                allDay: true,
                color: $categoria->color,
                tipo: 'actividad',
                editable: false,
                extendedProps: [
                    'categoria_id' => $categoria->id_categoria,
                    'categoria_nombre' => $categoria->nombre,
                    'icono' => $categoria->icono,
                    'curso' => $asignacion?->curso?->nombre_curso,
                    'materia' => $asignacion?->materia?->nombre_materia,
                    'tipo_actividad' => $actividad->tipo?->nombre_tipo,
                ],
            );
        })->all();
    }
}
