<?php

namespace Tests\Feature\Aislamiento;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * B2 — migraciones de expansión M1–M4 (docs/seguridad/etapa2-b2.md).
 * Corre en ambos motores sobre el esquema que crea RefreshDatabase. No ejecuta
 * DDL dentro de las pruebas (en MariaDB rompería la transacción de prueba): la
 * reversión y la reanudación se ensayan aparte, en colegio_tests.
 */
class Etapa2MigracionesExpansionTest extends AislamientoTestCase
{
    private const TABLAS = [
        // M1
        'materias', 'areas', 'periodos_academicos', 'tipos_actividad', 'escala_notas', 'evento_categorias',
        // M2
        'estudiantes', 'profesores', 'acudientes', 'eventos', 'soportes',
        // M3
        'colegio_configuracion', 'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias',
    ];

    public function test_las_16_tablas_tienen_id_institucion_nullable_con_indice_y_fk_restrict(): void
    {
        foreach (self::TABLAS as $tabla) {
            $col = collect(Schema::getColumns($tabla))->firstWhere('name', 'id_institucion');
            $this->assertNotNull($col, "$tabla.id_institucion no existe");
            $this->assertTrue($col['nullable'], "$tabla.id_institucion debe ser nullable");
            // MariaDB informa el default nulo como la cadena 'NULL'; SQLite como null
            $this->assertContains($col['default'], [null, 'NULL'], "$tabla.id_institucion sin default");
            $this->assertStringContainsStringIgnoringCase('int', $col['type_name']);

            $this->assertTrue(Schema::hasIndex($tabla, "{$tabla}_id_institucion_index"), "$tabla sin índice");

            $fk = collect(Schema::getForeignKeys($tabla))->first(fn ($f) => $f['columns'] === ['id_institucion']);
            $this->assertNotNull($fk, "$tabla sin FK");
            $this->assertSame('instituciones', $fk['foreign_table']);
            $this->assertSame(['id_institucion'], $fk['foreign_columns']);
            $this->assertContains(strtolower($fk['on_delete']), ['restrict', 'no action'], "$tabla: ON DELETE debe ser RESTRICT");
        }
    }

    public function test_no_se_puebla_nada_las_columnas_nuevas_quedan_en_null(): void
    {
        // B2 no puebla nada. Desde B5, los perfiles, eventos y soportes NUEVOS copian al
        // crearse la institución de su dueño (coherente con su usuario); los catálogos
        // creados sin sesión (factories) y la configuración/landing siguen en NULL.
        $conDueno = ['estudiantes' => 'id_usuario', 'profesores' => 'id_usuario', 'acudientes' => 'id_usuario', 'eventos' => 'id_usuario_creador', 'soportes' => 'id_usuario'];
        foreach (self::TABLAS as $tabla) {
            if (isset($conDueno[$tabla])) {
                $incoherentes = DB::table($tabla)->join('usuarios as u', 'u.id_usuario', '=', "{$tabla}.{$conDueno[$tabla]}")
                    ->whereNotNull("{$tabla}.id_institucion")->whereColumn("{$tabla}.id_institucion", '<>', 'u.id_institucion')->count();
                $this->assertSame(0, $incoherentes, "$tabla: la institución copiada debe ser la de su dueño");
            } else {
                $this->assertSame(0, DB::table($tabla)->whereNotNull('id_institucion')->count(), "$tabla tiene id_institucion poblado");
            }
        }
        $this->assertGreaterThan(0, DB::table('materias')->count());
        $this->assertGreaterThan(0, DB::table('estudiantes')->count());
    }

    public function test_m4_crea_migracion_pertenencia_vacia_y_sin_fks(): void
    {
        $this->assertTrue(Schema::hasTable('migracion_pertenencia'));
        $this->assertSame(0, DB::table('migracion_pertenencia')->count());
        $this->assertSame([], Schema::getForeignKeys('migracion_pertenencia'));
        foreach (['lote', 'accion', 'tabla', 'id_registro', 'columna', 'valor_anterior', 'valor_nuevo', 'id_institucion', 'id_registro_nuevo', 'origen', 'regla', 'ejecutado_por', 'created_at', 'revertido_at'] as $c) {
            $this->assertTrue(Schema::hasColumn('migracion_pertenencia', $c), "falta $c");
        }
    }

    public function test_restricciones_existentes_no_cambian(): void
    {
        // B2 no cambia únicos. R2a (B4, solo en pruebas) sustituye los de las 6
        // tablas clonables por compuestos; los de identidad siguen globales.
        $unicos = fn ($t) => collect(Schema::getIndexes($t))->where('unique', true)->where('primary', false)->pluck('columns')->all();
        $this->assertContains(['id_institucion', 'nombre_materia'], $unicos('materias'));
        $this->assertNotContains(['nombre_materia'], $unicos('materias'));
        $this->assertContains(['id_institucion', 'clave'], $unicos('landing_contenido'));
        $this->assertContains(['codigo_estudiante'], $unicos('estudiantes'));
        $this->assertContains(['codigo_profesor'], $unicos('profesores'));
        // cursos.id_institucion sigue con SET NULL (R3 no autorizado)
        $fk = collect(Schema::getForeignKeys('cursos'))->first(fn ($f) => $f['columns'] === ['id_institucion']);
        $this->assertSame('set null', strtolower($fk['on_delete']));
    }

    public function test_down_de_m1_a_m3_se_niega_si_ya_hay_pertenencia_asignada(): void
    {
        $idInst = $this->e->a['institucion']->id_institucion;
        DB::table('materias')->where('id_materia', $this->e->a['materia']->id_materia)->update(['id_institucion' => $idInst]);

        $m1 = require database_path('migrations/2026_11_18_100000_etapa2_m1_institucion_en_catalogos.php');
        try {
            $m1->down();
            $this->fail('down() debió negarse');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('materias.id_institucion', $e->getMessage());
        }
        $this->assertTrue(Schema::hasColumn('materias', 'id_institucion'), 'no debe haberse eliminado nada');
        $this->assertTrue(Schema::hasColumn('areas', 'id_institucion'));
    }

    public function test_down_de_m4_se_niega_si_hay_trazabilidad(): void
    {
        DB::table('migracion_pertenencia')->insert(['lote' => '00000000-0000-0000-0000-000000000001', 'accion' => 'descartar', 'tabla' => 'materias', 'id_registro' => 1, 'origen' => 'regla']);
        $m4 = require database_path('migrations/2026_11_18_100003_etapa2_m4_create_migracion_pertenencia_table.php');

        $this->expectException(RuntimeException::class);
        try {
            $m4->down();
        } finally {
            $this->assertTrue(Schema::hasTable('migracion_pertenencia'));
        }
    }
}
