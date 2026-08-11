<?php

namespace App\Modules\Calendario\Services\Occurrences;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Contracts\CalendarioOccurrenceSource;
use App\Modules\Calendario\DTO\CalendarioItem;
use App\Modules\Calendario\Models\EventoCategoria;
use App\Modules\Calendario\Services\VisibilidadCalendarioService;
use App\Modules\Calificaciones\Models\Periodo;
use App\Modules\GestionAcademica\Models\Horario;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Deriva ocurrencias fechadas a partir de `horarios` (plantilla semanal
 * recurrente, sin fechas propias) acotadas a las ventanas de fecha reales
 * de `periodos_academicos` del mismo año lectivo — así las clases no
 * aparecen durante vacaciones/recesos entre períodos, sin configuración
 * adicional. Nunca se duplica el horario en una tabla de ocurrencias.
 */
class HorarioOccurrenceSource implements CalendarioOccurrenceSource
{
    /** dayOfWeekIso (1=lunes..7=domingo) -> valor de Horario::DIAS_SEMANA. Evita depender del locale de Carbon. */
    private const DIA_ISO_A_NOMBRE = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
    ];

    public function __construct(private VisibilidadCalendarioService $visibilidad) {}

    public function occurrencesBetween(
        CarbonImmutable $desde,
        CarbonImmutable $hasta,
        Usuario $usuario,
        array $filtros
    ): array {
        $categoria = EventoCategoria::where('slug', 'clases')->first();
        if (! $categoria || ! $this->visibilidad->puedeVerCategoria($usuario, $categoria)) {
            return [];
        }

        if ($this->categoriaExcluidaPorFiltro($categoria, $filtros)) {
            return [];
        }

        $cursosVisibles = $this->visibilidad->cursosVisibles($usuario);
        if (is_array($cursosVisibles) && $cursosVisibles === []) {
            return [];
        }

        $claveCache = $this->claveCache($desde, $hasta, $cursosVisibles, $filtros);

        // Se cachean los DTO (no arrays): son readonly y serializan sin
        // problema, y así el contrato de la interfaz no cambia según haya
        // o no cache-hit.
        return Cache::remember(
            $claveCache,
            now()->addHours(6),
            fn () => $this->calcularOcurrencias($desde, $hasta, $cursosVisibles, $filtros, $categoria)
        );
    }

    /**
     * Clave versionada (no TTL ciego): `horarios_version` se incrementa en
     * HorariosController cada vez que se muta un horario, así que
     * un cambio administrativo invalida el caché al instante sin esperar
     * a que expire, y sin necesidad de Cache::tags (el driver `database`
     * no las soporta).
     */
    private function claveCache(CarbonImmutable $desde, CarbonImmutable $hasta, ?array $cursosVisibles, array $filtros): string
    {
        $version = Cache::get('calendario:horarios_version', 1);
        $huellaFiltros = md5(json_encode([$cursosVisibles, $filtros['curso'] ?? null, $filtros['categorias'] ?? null]));

        return "calendario:horarios:v{$version}:{$desde->toDateString()}:{$hasta->toDateString()}:{$huellaFiltros}";
    }

    private function calcularOcurrencias(
        CarbonImmutable $desde,
        CarbonImmutable $hasta,
        ?array $cursosVisibles,
        array $filtros,
        EventoCategoria $categoria
    ): array {
        $horarios = Horario::query()
            ->where('estado', 'activo')
            ->whereHas('asignacion', function ($query) use ($cursosVisibles, $filtros) {
                $query->where('estado', 'activo');
                if (is_array($cursosVisibles)) {
                    $query->whereIn('id_curso', $cursosVisibles);
                }
                if (! empty($filtros['curso'])) {
                    $query->where('id_curso', $filtros['curso']);
                }
            })
            ->with(['asignacion.materia', 'asignacion.profesor.usuario', 'asignacion.curso'])
            ->get();

        if ($horarios->isEmpty()) {
            return [];
        }

        $ventanasPorAnio = $this->ventanasDePeriodoPorAnio($horarios);

        $items = [];
        foreach ($horarios as $horario) {
            $asignacion = $horario->asignacion;
            if (! $asignacion) {
                continue;
            }

            $ventanas = $ventanasPorAnio->get($asignacion->anio_lectivo, collect());
            if ($ventanas->isEmpty()) {
                continue;
            }

            foreach ($this->fechasDelHorario($horario, $desde, $hasta, $ventanas) as $fecha) {
                $items[] = new CalendarioItem(
                    id: "horario:{$horario->id_horario}:{$fecha->toDateString()}",
                    title: $asignacion->materia?->nombre_materia ?? 'Clase',
                    start: $fecha->toDateString().'T'.$horario->hora_inicio,
                    end: $fecha->toDateString().'T'.$horario->hora_fin,
                    allDay: false,
                    color: $categoria->color,
                    tipo: 'horario',
                    editable: false,
                    extendedProps: [
                        'categoria_id' => $categoria->id_categoria,
                        'categoria_nombre' => $categoria->nombre,
                        'icono' => $categoria->icono,
                        'curso' => $asignacion->curso?->nombre_curso,
                        'docente' => $asignacion->profesor?->usuario?->nombres.' '.$asignacion->profesor?->usuario?->apellidos,
                        'salon' => $horario->salon,
                    ],
                );
            }
        }

        return $items;
    }

    private function categoriaExcluidaPorFiltro(EventoCategoria $categoria, array $filtros): bool
    {
        if (empty($filtros['categorias'])) {
            return false;
        }

        return ! in_array($categoria->id_categoria, $filtros['categorias'], true);
    }

    /** @return Collection<int, Collection<int, array{fecha_inicio: string, fecha_fin: string}>> */
    private function ventanasDePeriodoPorAnio(Collection $horarios): Collection
    {
        $anios = $horarios->pluck('asignacion.anio_lectivo')->filter()->unique()->values();

        return Periodo::whereIn('anio_lectivo', $anios)
            ->get(['anio_lectivo', 'fecha_inicio', 'fecha_fin'])
            ->groupBy('anio_lectivo');
    }

    /** @return CarbonImmutable[] */
    private function fechasDelHorario(Horario $horario, CarbonImmutable $desde, CarbonImmutable $hasta, Collection $ventanas): array
    {
        $fechas = [];

        for ($fecha = $desde; $fecha->lte($hasta); $fecha = $fecha->addDay()) {
            $nombreDia = self::DIA_ISO_A_NOMBRE[$fecha->dayOfWeekIso] ?? null;
            if ($nombreDia !== $horario->dia_semana) {
                continue;
            }

            if ($this->fechaDentroDeAlgunaVentana($fecha, $ventanas)) {
                $fechas[] = $fecha;
            }
        }

        return $fechas;
    }

    private function fechaDentroDeAlgunaVentana(CarbonImmutable $fecha, Collection $ventanas): bool
    {
        foreach ($ventanas as $ventana) {
            if ($fecha->toDateString() >= $ventana->fecha_inicio && $fecha->toDateString() <= $ventana->fecha_fin) {
                return true;
            }
        }

        return false;
    }
}
