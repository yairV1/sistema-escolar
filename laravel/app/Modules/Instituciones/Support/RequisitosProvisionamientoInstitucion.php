<?php

namespace App\Modules\Instituciones\Support;

use App\Modules\Instituciones\Auditoria\AuditoriaPertenencia;
use App\Modules\Instituciones\Poblacion\SimuladorPoblacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RequisitosProvisionamientoInstitucion
{
    private const TABLAS_NOT_NULL_B8 = [
        'cursos', 'solicitudes_admision', 'materias', 'areas', 'periodos_academicos',
        'tipos_actividad', 'estudiantes', 'profesores', 'acudientes', 'eventos', 'soportes',
        'colegio_configuracion', 'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias',
    ];

    private const TABLAS_FK_B8 = [
        ...self::TABLAS_NOT_NULL_B8,
        'escala_notas', 'evento_categorias',
    ];

    private const UNICOS = [
        'instituciones' => [['slug']],
        'usuarios' => [['correo'], ['numero_documento']],
        'materias' => [['id_institucion', 'nombre_materia']],
        'areas' => [['id_institucion', 'nombre_area']],
        'tipos_actividad' => [['id_institucion', 'nombre_tipo']],
        'periodos_academicos' => [['id_institucion', 'nombre_periodo', 'anio_lectivo']],
        'evento_categorias' => [['id_institucion', 'slug']],
        'landing_contenido' => [['id_institucion', 'clave']],
        'cursos' => [['id_institucion', 'nombre_curso', 'anio_lectivo', 'jornada']],
        'estudiantes' => [['id_institucion', 'codigo_estudiante']],
        'profesores' => [['id_institucion', 'codigo_profesor']],
        'colegio_configuracion' => [['id_institucion']],
    ];

    public static function pendientes(): array
    {
        $schema = Schema::connection(DB::connection()->getName());
        $pendientes = [];

        foreach (array_keys(SimuladorPoblacion::TABLAS) as $tabla) {
            if (! $schema->hasColumn($tabla, 'id_institucion')) {
                $pendientes[] = "Falta B2–B7: {$tabla}.id_institucion.";
            }
        }

        if (! $schema->hasTable('migracion_pertenencia')) {
            $pendientes[] = 'Falta B2–B7: no existe migracion_pertenencia.';
        }

        if (! $schema->hasColumn('usuarios', 'id_institucion')) {
            $pendientes[] = 'Falta esquema institucional: usuarios.id_institucion.';
        }

        $tablasConFk = array_unique([
            ...array_keys(SimuladorPoblacion::TABLAS),
            ...self::TABLAS_FK_B8,
        ]);
        foreach ($tablasConFk as $tabla) {
            if (! $schema->hasTable($tabla) || ! $schema->hasColumn($tabla, 'id_institucion')) {
                $pendientes[] = "Falta B2–B8: {$tabla}.id_institucion.";

                continue;
            }

            $columna = collect($schema->getColumns($tabla))->firstWhere('name', 'id_institucion');
            if (in_array($tabla, self::TABLAS_NOT_NULL_B8, true)
                && ($columna === null || $columna['nullable'])) {
                $pendientes[] = "Falta B8: {$tabla}.id_institucion debe ser NOT NULL.";
            }

            $fk = collect($schema->getForeignKeys($tabla))
                ->first(fn ($foreignKey) => $foreignKey['columns'] === ['id_institucion']
                    && $foreignKey['foreign_table'] === 'instituciones');
            if ($fk === null || ! in_array(strtolower((string) $fk['on_delete']), ['restrict', 'no action'], true)) {
                $pendientes[] = "Falta B8: FK segura de {$tabla}.id_institucion.";
            }
        }

        foreach (self::UNICOS as $tabla => $conjuntos) {
            if (! $schema->hasTable($tabla)) {
                $pendientes[] = "Falta esquema requerido: tabla {$tabla}.";

                continue;
            }

            $indices = $schema->getIndexes($tabla);
            foreach ($conjuntos as $columnas) {
                if (! collect($indices)->contains(fn ($indice) => $indice['unique']
                    && $indice['columns'] === $columnas)) {
                    $pendientes[] = 'Falta restricción única: '.implode(', ', [$tabla, ...$columnas]).'.';
                }
            }
        }

        if ($pendientes !== []) {
            return array_values(array_unique($pendientes));
        }

        $reporte = (new AuditoriaPertenencia(DB::connection()))->ejecutar();
        foreach ($reporte['hallazgos'] as $hallazgo) {
            if ($hallazgo['severidad'] === AuditoriaPertenencia::CRITICO
                && in_array($hallazgo['seccion'], [
                    'relaciones_cruzadas',
                    'referencias_huerfanas',
                    'coherencia_institucional',
                    'sin_institucion',
                    'configuracion',
                    'restricciones_unicas',
                ], true)) {
                $pendientes[] = 'La auditoría de pertenencia detectó integridad pendiente en '.$hallazgo['tabla'].'.';
            }
        }

        return array_values(array_unique($pendientes));
    }
}
