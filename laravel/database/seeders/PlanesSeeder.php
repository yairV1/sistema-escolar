<?php

namespace Database\Seeders;

use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Modulos\Models\Modulo;
use App\Modules\Planes\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Catálogo inicial de planes comerciales + backfill de
 * `instituciones.id_plan` a partir del `plan` de texto existente
 * ('basico'/'estandar'/'premium', ver InstitucionStoreRequest). Debe
 * correr después de ModulosSeeder (asigna módulos por plan) y después de
 * que `instituciones` ya tenga datos (correo en DatabaseSeeder, después
 * de ColegioConfiguracionSeeder implícitamente vía la migración que crea
 * la institución #1). Idempotente por slug.
 */
class PlanesSeeder extends Seeder
{
    /** Mapea el valor legado `instituciones.plan` (texto) al slug del catálogo nuevo. */
    private const MAPA_PLAN_LEGADO = [
        'basico' => 'Básico',
        'estandar' => 'Estandar',
        'premium' => 'Premium',
    ];

    public function run(): void
    {
        foreach ($this->catalogo() as $orden => $datos) {
            $modulos = $datos['modulos'];
            unset($datos['modulos']);

            $plan = Plan::updateOrCreate(['slug' => $datos['slug']], [...$datos, 'orden' => $orden]);

            $idsModulos = Modulo::whereIn('slug', $modulos)->pluck('id_modulo');
            $plan->modulos()->sync($idsModulos);
        }

        $this->backfillInstituciones();
    }

    private function backfillInstituciones(): void
    {
        foreach (self::MAPA_PLAN_LEGADO as $planTexto => $slugCatalogo) {
            $idPlan = Plan::where('slug', $slugCatalogo)->value('id_plan');

            if ($idPlan === null) {
                continue;
            }

            Institucion::where('plan', $planTexto)->whereNull('id_plan')->update(['id_plan' => $idPlan]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function catalogo(): array
    {
        return [
            [
                'nombre' => 'Básico',
                'slug' => 'Básico',
                'descripcion' => 'Colegios de un solo campus, hasta 300 estudiantes.',
                'precio_mensual' => 400000,
                'precio_anual' => 1200000,
                'limite_usuarios' => 300,
                'beneficios' => [
                    'Registro de estudiantes y docentes',
                    'Boletines',
                    'Horario',
                    'Asistencia',
                    'Notas',
                    'Director de curso',
                    'Rol directivo y docente',
                ],
                'estado' => 'activo',
                'modulos' => ['matriculas', 'boletines', 'horarios'],
            ],
            [
                'nombre' => 'Estandar',
                'slug' => 'Estandar',
                'descripcion' => 'Colegios en crecimiento, multi-sede, hasta 1.500 estudiantes.',
                'precio_mensual' => 800000,
                'precio_anual' => 2000000,
                'limite_usuarios' => 1500,
                'beneficios' => [
                    'Todo lo del plan Básico',
                    'Rol de estudiante y acudiente',
                    'Comunicados',
                    'Observaciones del estudiante',
                    'Hasta 10 usuarios administrativos',
                ],
                'estado' => 'activo',
                'modulos' => ['matriculas', 'boletines', 'horarios', 'comunicados', 'portal-de-padres'],
            ],
            [
                'nombre' => 'Premium',
                'slug' => 'Premium',
                'descripcion' => 'Redes de colegios y estudiantado ilimitado.',
                'precio_mensual' => 1500000,
                'precio_anual' => 2500000,
                'limite_usuarios' => null,
                'beneficios' => [
                    'Todo lo del plan Estandar',
                    'Sitio web institucional administrable',
                    'Estadísticas avanzadas',
                    'Sedes y estudiantes ilimitados',
                    'Soporte dedicado',
                ],
                'estado' => 'activo',
                'modulos' => ['matriculas', 'boletines', 'horarios', 'comunicados', 'portal-de-padres'],
            ],
        ];
    }
}
