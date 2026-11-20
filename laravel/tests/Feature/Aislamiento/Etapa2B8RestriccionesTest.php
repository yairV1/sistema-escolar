<?php

namespace Tests\Feature\Aislamiento;

use App\Core\Seguridad\GuardiaBaseDePruebas;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class Etapa2B8RestriccionesTest extends AislamientoTestCase
{
    private const MIGRACION = 'migrations/etapa2/b8/2026_11_19_120000_etapa2_b8_restricciones_institucionales.php';

    public function test_bloquea_filas_ambiguas_antes_de_cualquier_ddl(): void
    {
        $antes = $this->huella('landing_contenido');
        $migration = $this->migracion();

        try {
            $migration->up();
            $this->fail('B8 debió rechazar la landing aún sin institución');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('landing_contenido', $e->getMessage());
        }

        $this->assertSame($antes, $this->huella('landing_contenido'));
        $this->assertTrue(collect(Schema::getColumns('colegio_configuracion'))->firstWhere('name', 'id_institucion')['nullable']);
        $this->assertNotNull($this->indiceUnico('cursos', ['nombre_curso', 'anio_lectivo', 'jornada']));
        $this->assertNull($this->indiceUnico('cursos', ['id_institucion', 'nombre_curso', 'anio_lectivo', 'jornada']));
    }

    public function test_bloquea_relaciones_cruzadas_antes_de_cambiar_restricciones(): void
    {
        $this->completarPertenencias();
        DB::table('asignaciones_academicas')
            ->where('id_asignacion', $this->e->b['asignacion']->id_asignacion)
            ->update(['id_curso' => $this->e->a['curso']->id_curso]);

        $migration = $this->migracion();
        try {
            $migration->up();
            $this->fail('B8 debió rechazar la asignación entre colegios');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('integridad padre-hijo', $e->getMessage());
        }

        $this->assertNotNull($this->indiceUnico('cursos', ['nombre_curso', 'anio_lectivo', 'jornada']));
        $this->assertNull($this->indiceUnico('cursos', ['id_institucion', 'nombre_curso', 'anio_lectivo', 'jornada']));
    }

    public function test_aplica_r1_r2_r3_preserva_datos_y_revierte_solo_si_no_hay_colisiones(): void
    {
        $this->completarPertenencias();
        $antes = $this->filasInstitucionales();
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $fkUsuario = collect(Schema::getForeignKeys('usuarios'))
                ->first(fn ($fk) => $fk['columns'] === ['id_institucion'] && $fk['foreign_table'] === 'instituciones');
            if ($fkUsuario !== null) {
                Schema::table('usuarios', fn ($tabla) => $tabla->dropForeign(['id_institucion']));
            }
        }
        $migration = $this->migracion();
        $migration->up();

        $despues = $this->filasInstitucionales();
        $this->assertSame($antes, $despues, 'DDL no debe cambiar filas ni valores');

        foreach ([
            'cursos', 'solicitudes_admision', 'materias', 'areas', 'periodos_academicos',
            'tipos_actividad', 'estudiantes', 'profesores', 'acudientes', 'eventos', 'soportes',
            'colegio_configuracion', 'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias',
        ] as $tabla) {
            $this->assertFalse(collect(Schema::getColumns($tabla))->firstWhere('name', 'id_institucion')['nullable'], "{$tabla}.id_institucion debe ser NOT NULL");
        }
        foreach (['evento_categorias', 'escala_notas'] as $tabla) {
            $this->assertTrue(collect(Schema::getColumns($tabla))->firstWhere('name', 'id_institucion')['nullable'], "{$tabla} conserva NULL para plantillas globales");
        }
        $this->assertTrue(collect(Schema::getColumns('usuarios'))->firstWhere('name', 'id_institucion')['nullable']);
        $this->assertNull($this->e->superadmin->fresh()->id_institucion);
        $this->assertContains(strtolower($this->fkDelete('cursos')), ['restrict', 'no action']);
        $this->assertContains(strtolower($this->fkDelete('solicitudes_admision')), ['restrict', 'no action']);
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->assertContains(strtolower($this->fkDelete('usuarios')), ['restrict', 'no action']);
            $this->assertTrue(collect(Schema::getIndexes('usuarios'))->contains(fn ($i) => $i['columns'] === ['id_institucion']));
            $this->assertDatabaseConstraint(fn () => DB::table('usuarios')
                ->where('id_usuario', $this->e->a['directivo']->id_usuario)
                ->update(['id_institucion' => PHP_INT_MAX]));
        }

        foreach ([
            'cursos' => ['id_institucion', 'nombre_curso', 'anio_lectivo', 'jornada'],
            'estudiantes' => ['id_institucion', 'codigo_estudiante'],
            'profesores' => ['id_institucion', 'codigo_profesor'],
            'colegio_configuracion' => ['id_institucion'],
        ] as $tabla => $columnas) {
            $this->assertNotNull($this->indiceUnico($tabla, $columnas));
        }
        foreach (['correo', 'numero_documento'] as $columna) {
            $this->assertNotNull($this->indiceUnico('usuarios', [$columna]), "usuarios.{$columna} sigue siendo globalmente único");
        }

        $idA = $this->e->a['institucion']->id_institucion;
        $idB = $this->e->b['institucion']->id_institucion;
        $cursoA = $this->insertarCurso($idA);
        $cursoB = $this->insertarCurso($idB);
        $this->assertNotSame($cursoA, $cursoB, 'Un curso con nombre legítimamente repetido cabe en cada colegio');
        $this->assertDatabaseConstraint(fn () => $this->insertarCurso($idA));

        $this->assertDatabaseConstraint(fn () => DB::table('colegio_configuracion')->insert([
            'nombre_colegio' => 'Duplicada Alfa', 'id_institucion' => $idA, 'created_at' => now(), 'updated_at' => now(),
        ]));

        $estA = $this->crearUsuarioYEstudiante($idA, 'CODIGO-REPETIDO');
        $estB = $this->crearUsuarioYEstudiante($idB, 'CODIGO-REPETIDO');
        $this->assertNotSame($estA, $estB);
        $this->assertDatabaseConstraint(fn () => $this->crearUsuarioYEstudiante($idA, 'CODIGO-REPETIDO'));
        $profA = $this->crearUsuarioYProfesor($idA, 'DOC-REPETIDO');
        $profB = $this->crearUsuarioYProfesor($idB, 'DOC-REPETIDO');
        $this->assertNotSame($profA, $profB);
        $this->assertDatabaseConstraint(fn () => $this->crearUsuarioYProfesor($idA, 'DOC-REPETIDO'));

        UsuarioFactory::new()->create([
            'id_institucion' => $idA, 'correo' => 'global@example.test', 'numero_documento' => 'GLOBAL-1',
        ]);
        $this->assertDatabaseConstraint(fn () => UsuarioFactory::new()->create([
            'id_institucion' => $idB, 'correo' => 'global@example.test', 'numero_documento' => 'GLOBAL-2',
        ]));
        $this->assertDatabaseConstraint(fn () => UsuarioFactory::new()->create([
            'id_institucion' => $idB, 'correo' => 'global-2@example.test', 'numero_documento' => 'GLOBAL-1',
        ]));

        $this->assertDatabaseConstraint(fn () => DB::table('instituciones')->where('id_institucion', $idA)->delete());

        try {
            $migration->down();
            $this->fail('down() debe bloquear colisiones que no caben en únicos globales');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('colisionan', $e->getMessage());
        }
        $this->assertNotNull($this->indiceUnico('cursos', ['id_institucion', 'nombre_curso', 'anio_lectivo', 'jornada']));
        $this->assertFalse(collect(Schema::getColumns('cursos'))->firstWhere('name', 'id_institucion')['nullable']);

        DB::table('cursos')->whereIn('id_curso', [$cursoA, $cursoB])->delete();
        DB::table('estudiantes')->whereIn('id_estudiante', [$estA, $estB])->delete();
        DB::table('profesores')->whereIn('id_profesor', [$profA, $profB])->delete();
        $migration->down();

        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->assertSame('set null', strtolower($this->fkDelete('usuarios')));
        }
        $this->assertNotNull($this->indiceUnico('cursos', ['nombre_curso', 'anio_lectivo', 'jornada']));
        $this->assertNull($this->indiceUnico('cursos', ['id_institucion', 'nombre_curso', 'anio_lectivo', 'jornada']));
        $this->assertTrue(collect(Schema::getColumns('cursos'))->firstWhere('name', 'id_institucion')['nullable']);
        $this->assertSame('set null', strtolower($this->fkDelete('cursos')));
        $this->assertSame($antes, $this->filasInstitucionales(), 'Los datos previos sobreviven a up/down');
    }

    private function migracion(): Migration
    {
        GuardiaBaseDePruebas::asegurar(DB::connection(), 'B8 test');

        return require database_path(self::MIGRACION);
    }

    private function completarPertenencias(): void
    {
        $idA = $this->e->a['institucion']->id_institucion;
        $idB = $this->e->b['institucion']->id_institucion;

        $this->clonarCatalogo('materias', 'id_materia', $idA, $idB, function ($anterior, $nuevo) {
            DB::table('asignaciones_academicas')->where('id_curso', $this->e->b['curso']->id_curso)
                ->where('id_materia', $anterior)->update(['id_materia' => $nuevo]);
            DB::table('profesor_materia')->where('id_materia', $anterior)
                ->where('id_profesor', $this->e->b['profesor']->id_profesor)->update(['id_materia' => $nuevo]);
        });

        $this->clonarCatalogo('periodos_academicos', 'id_periodo', $idA, $idB, function ($anterior, $nuevo) {
            $asignaciones = DB::table('asignaciones_academicas')->where('id_curso', $this->e->b['curso']->id_curso)->pluck('id_asignacion');
            DB::table('actividades')->whereIn('id_asignacion', $asignaciones)->where('id_periodo', $anterior)->update(['id_periodo' => $nuevo]);
            $estudiantes = DB::table('estudiantes')->where('id_institucion', $this->e->b['institucion']->id_institucion)->pluck('id_estudiante');
            DB::table('boletines')->whereIn('id_estudiante', $estudiantes)->where('id_periodo', $anterior)->update(['id_periodo' => $nuevo]);
        });

        $this->clonarCatalogo('tipos_actividad', 'id_tipo_actividad', $idA, $idB, function ($anterior, $nuevo) {
            $asignaciones = DB::table('asignaciones_academicas')->where('id_curso', $this->e->b['curso']->id_curso)->pluck('id_asignacion');
            DB::table('actividades')->whereIn('id_asignacion', $asignaciones)->where('id_tipo_actividad', $anterior)->update(['id_tipo_actividad' => $nuevo]);
        });

        foreach ([
            ['estudiantes', 'id_estudiante', 'id_usuario'],
            ['profesores', 'id_profesor', 'id_usuario'],
            ['acudientes', 'id_acudiente', 'id_usuario'],
            ['eventos', 'id_evento', 'id_usuario_creador'],
            ['soportes', 'id_soporte', 'id_usuario'],
        ] as [$tabla, $pk, $fkUsuario]) {
            $filas = DB::table($tabla.' as t')->join('usuarios as u', 'u.id_usuario', '=', "t.{$fkUsuario}")
                ->select("t.{$pk} as id", 'u.id_institucion as institucion')->get();
            foreach ($filas as $fila) {
                DB::table($tabla)->where($pk, $fila->id)->update(['id_institucion' => $fila->institucion]);
            }
        }

        DB::table('colegio_configuracion')->where('id_configuracion', 1)->update(['id_institucion' => $idA]);
        DB::table('colegio_configuracion')->insert([
            'nombre_colegio' => 'Colegio Zeta', 'id_institucion' => $idB, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('landing_contenido')->whereNull('id_institucion')->update(['id_institucion' => $idA]);
        DB::table('landing_galeria')->whereNull('id_institucion')->update(['id_institucion' => $idA]);
        DB::table('landing_noticias')->whereNull('id_institucion')->update(['id_institucion' => $idA]);
    }

    private function clonarCatalogo(string $tabla, string $pk, int $idA, int $idB, callable $repuntar): void
    {
        foreach (DB::table($tabla)->get() as $fila) {
            $registro = (array) $fila;
            $anterior = $fila->{$pk};
            DB::table($tabla)->where($pk, $anterior)->update(['id_institucion' => $idA]);
            unset($registro[$pk]);
            $registro['id_institucion'] = $idB;
            $nuevo = DB::table($tabla)->insertGetId($registro, $pk);
            $repuntar($anterior, $nuevo);
        }
    }

    private function insertarCurso(int $idInstitucion): int
    {
        return (int) DB::table('cursos')->insertGetId([
            'nombre_curso' => 'Curso Compartido',
            'nivel_academico' => 'secundaria',
            'jornada' => 'manana',
            'anio_lectivo' => 2026,
            'estado' => 'activo',
            'id_institucion' => $idInstitucion,
        ], 'id_curso');
    }

    private function crearUsuarioYEstudiante(int $idInstitucion, string $codigo): int
    {
        $usuario = UsuarioFactory::new()->create(['id_rol' => 6, 'id_institucion' => $idInstitucion]);

        return (int) DB::table('estudiantes')->insertGetId([
            'id_usuario' => $usuario->id_usuario,
            'id_institucion' => $idInstitucion,
            'codigo_estudiante' => $codigo,
            'fecha_nacimiento' => '2012-01-01',
            'genero' => 'F',
            'direccion' => 'Calle de prueba',
            'fecha_ingreso' => '2026-01-15',
            'estado_academico' => 'activo',
        ], 'id_estudiante');
    }

    private function crearUsuarioYProfesor(int $idInstitucion, string $codigo): int
    {
        $usuario = UsuarioFactory::new()->create(['id_rol' => 5, 'id_institucion' => $idInstitucion]);

        return (int) DB::table('profesores')->insertGetId([
            'id_usuario' => $usuario->id_usuario,
            'id_institucion' => $idInstitucion,
            'codigo_profesor' => $codigo,
            'estado_laboral' => 'activo',
        ], 'id_profesor');
    }

    private function assertDatabaseConstraint(callable $operacion): void
    {
        try {
            $operacion();
            $this->fail('La restricción de integridad debió rechazar el registro');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    private function indiceUnico(string $tabla, array $columnas): ?array
    {
        return collect(Schema::getIndexes($tabla))->first(fn ($i) => $i['unique'] && ! $i['primary'] && $i['columns'] === $columnas);
    }

    private function fkDelete(string $tabla): string
    {
        return (string) collect(Schema::getForeignKeys($tabla))
            ->first(fn ($f) => $f['columns'] === ['id_institucion'] && $f['foreign_table'] === 'instituciones')['on_delete'];
    }

    private function filasInstitucionales(): array
    {
        $tablas = [
            'cursos', 'solicitudes_admision', 'materias', 'areas', 'periodos_academicos', 'tipos_actividad',
            'estudiantes', 'profesores', 'acudientes', 'eventos', 'soportes', 'colegio_configuracion',
            'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias',
        ];

        return array_combine($tablas, array_map(fn ($tabla) => DB::table($tabla)->get()->map(fn ($fila) => (array) $fila)
            ->sortBy(fn ($fila) => json_encode($fila))->values()->all(), $tablas));
    }
}
