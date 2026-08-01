<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Comunicados\Models\Notificacion;
use App\Modules\GestionAcademica\Models\Horario;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Modulos\Models\Modulo;
use App\Modules\Planes\Models\Plan;
use App\Modules\Soporte\Models\Soporte;
use Illuminate\Support\Collection;

/**
 * Agregaciones para el dashboard de SuperAdmin. Se justifica un Service
 * (no un controlador con SQL disperso, docs/arquitectura/08-estandares.md
 * §5.2) porque coordina varios módulos de negocio a la vez — mismo rol que
 * cumpliría un módulo de Reportes para el panel institucional.
 */
class PlataformaMetricasService
{
    public function resumen(): array
    {
        return [
            'instituciones_activas' => Institucion::where('estado', 'activa')->count(),
            'instituciones_suspendidas' => Institucion::where('estado', 'suspendida')->count(),
            'instituciones_total' => Institucion::count(),
            'usuarios_totales' => Usuario::where('id_rol', '!=', 8)->count(),
            'usuarios_por_rol' => $this->usuariosPorRol(),
            'comunicados_enviados' => Notificacion::count(),
            'eventos_creados' => Evento::count(),
            'horarios_creados' => Horario::count(),
            'soporte_nuevos' => Soporte::where('estado', 'nuevo')->count(),
            'planes_activos' => Plan::where('estado', 'activo')->count(),
            'modulos_activos' => Modulo::where('estado', 'activo')->count(),
        ];
    }

    /** Cuántas instituciones tiene cada plan del catálogo — alimenta el widget "Mezcla de planes" del dashboard. */
    public function planesDistribucion(): Collection
    {
        return Institucion::query()
            ->whereNotNull('id_plan')
            ->selectRaw('id_plan, count(*) as total')
            ->groupBy('id_plan')
            ->with('planCatalogo')
            ->get()
            ->map(fn (Institucion $fila) => [
                'plan' => $fila->planCatalogo?->nombre ?? 'Sin plan',
                'total' => $fila->total,
            ])
            ->sortByDesc('total')
            ->values();
    }

    public function usuariosPorRol(): Collection
    {
        return Usuario::selectRaw('id_rol, count(*) as total')
            ->groupBy('id_rol')
            ->with('rol')
            ->get()
            ->map(fn ($fila) => [
                'rol' => $fila->rol?->nombre_rol ?? 'Desconocido',
                'total' => $fila->total,
            ]);
    }

    /** Instituciones nuevas por mes, últimos 12 meses — serie para el gráfico de crecimiento. */
    public function institucionesNuevasPorMes(): Collection
    {
        return $this->conteoPorMes(Institucion::query(), 'created_at');
    }

    /** Comunicados enviados por mes, últimos 12 meses — serie de uso de la plataforma. */
    public function comunicadosPorMes(): Collection
    {
        return $this->conteoPorMes(Notificacion::query(), 'fecha_envio');
    }

    /** @return array<int, Institucion> instituciones a menos de 30 días de vencer su licencia. */
    public function institucionesProximasAVencer(): array
    {
        return Institucion::where('estado', 'activa')
            ->whereNotNull('fecha_vencimiento')
            ->get()
            ->filter(fn (Institucion $institucion) => $institucion->proximaAVencer())
            ->values()
            ->all();
    }

    private function conteoPorMes($query, string $columnaFecha): Collection
    {
        $desde = now()->subMonths(11)->startOfMonth();

        $porMes = $query
            ->where($columnaFecha, '>=', $desde)
            ->selectRaw("DATE_FORMAT($columnaFecha, '%Y-%m') as mes, count(*) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $serie = collect();
        for ($i = 11; $i >= 0; $i--) {
            $mes = now()->subMonths($i)->format('Y-m');
            $serie->put($mes, $porMes->get($mes, 0));
        }

        return $serie;
    }
}
