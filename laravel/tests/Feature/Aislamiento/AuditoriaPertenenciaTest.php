<?php

namespace Tests\Feature\Aislamiento;

use App\Core\Seguridad\SoloLectura;
use App\Modules\Instituciones\Auditoria\AuditoriaPertenencia;
use Database\Factories\MateriaFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Group;
use Throwable;

/**
 * B1 — institucion:auditar: detección de cada tipo de hallazgo, solo lectura
 * garantizada y plantilla de correspondencia sin decisiones.
 */
class AuditoriaPertenenciaTest extends AislamientoTestCase
{
    private function auditar(): array
    {
        return (new AuditoriaPertenencia(DB::connection()))->ejecutar();
    }

    private function hallazgos(array $informe, string $seccion, ?string $tabla = null): array
    {
        return array_values(array_filter($informe['hallazgos'], fn ($h) => $h['seccion'] === $seccion && ($tabla === null || $h['tabla'] === $tabla)));
    }

    public function test_escenario_limpio_sin_relaciones_cruzadas_ni_huerfanos(): void
    {
        $informe = $this->auditar();
        $this->assertSame([], $this->hallazgos($informe, 'relaciones_cruzadas'));
        $this->assertSame([], $this->hallazgos($informe, 'referencias_huerfanas'));
        $this->assertSame([], $this->hallazgos($informe, 'sin_institucion', 'usuarios'), 'el SuperAdmin sin institución no es un hallazgo');
    }

    public function test_detecta_relacion_entre_colegios(): void
    {
        DB::table('matriculas')->insert(['id_estudiante' => $this->e->b['estudiante']->id_estudiante, 'id_curso' => $this->e->a['curso7a']->id_curso, 'anio_lectivo' => 2025, 'fecha_matricula' => '2025-01-15', 'estado_matricula' => 'activa']);
        DB::table('observaciones')->insert(['id_estudiante' => $this->e->b['estudiante']->id_estudiante, 'id_profesor' => $this->e->a['profesor']->id_profesor, 'tipo_observacion' => 'academica', 'descripcion' => 'x', 'fecha' => '2026-11-16', 'nivel_gravedad' => 'baja', 'estado' => 'activa']);

        $informe = $this->auditar();
        $this->assertCount(1, $this->hallazgos($informe, 'relaciones_cruzadas', 'matriculas'));
        $this->assertCount(1, $this->hallazgos($informe, 'relaciones_cruzadas', 'observaciones'));
    }

    public function test_detecta_registros_sin_institucion(): void
    {
        DB::table('usuarios')->where('id_usuario', $this->e->a['coordinador']->id_usuario)->update(['id_institucion' => null]);
        DB::table('cursos')->where('id_curso', $this->e->a['curso7a']->id_curso)->update(['id_institucion' => null]);

        $informe = $this->auditar();
        $this->assertCount(1, $this->hallazgos($informe, 'sin_institucion', 'usuarios'));
        $this->assertCount(1, $this->hallazgos($informe, 'sin_institucion', 'cursos'));
    }

    public function test_clasifica_catalogos_compartidos_resolubles_y_ambiguos(): void
    {
        $sinUso = MateriaFactory::new()->create(['nombre_materia' => 'Sin uso']);
        $catalogo = collect($this->auditar()['catalogos']);
        $clase = fn ($tabla, $id) => $catalogo->first(fn ($c) => $c['tabla'] === $tabla && $c['id'] === $id)['clase'] ?? null;

        $this->assertSame('compartido', $clase('materias', $this->e->a['materia']->id_materia), 'Matemáticas la usan ambos colegios');
        $this->assertSame('resoluble', $clase('materias', $this->e->a['asignacion2']->id_materia), 'Ciencias solo la usa el colegio A');
        $this->assertSame('sin_uso_ambiguo', $clase('materias', $sinUso->id_materia), 'sin uso y con dos colegios: decisión manual');
        $this->assertSame('compartido', $clase('periodos_academicos', $this->e->a['periodo']->id_periodo));
    }

    /** Inyectar un huérfano exige desactivar FK; SQLite no lo permite dentro de la transacción de RefreshDatabase. */
    #[Group('mariadb')]
    public function test_detecta_referencias_huerfanas(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('matriculas')->insert(['id_estudiante' => 999999, 'id_curso' => $this->e->a['curso']->id_curso, 'anio_lectivo' => 2024, 'fecha_matricula' => '2024-01-15', 'estado_matricula' => 'activa']);
        Schema::enableForeignKeyConstraints();

        $huerfanos = $this->hallazgos($this->auditar(), 'referencias_huerfanas', 'matriculas');
        $this->assertCount(1, $huerfanos);
        $this->assertStringContainsString('id_estudiante = 999999', $huerfanos[0]['detalle']);
    }

    public function test_detecta_configuracion_compartida_y_unicos_globales(): void
    {
        // Las migraciones ya siembran la configuración única y las claves de la landing;
        // se garantiza que existan sin chocar con sus restricciones únicas.
        if (! DB::table('colegio_configuracion')->exists()) {
            DB::table('colegio_configuracion')->insert(['nombre_colegio' => 'Único', 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('landing_contenido')->insert(['clave' => 'clave_de_prueba_auditoria', 'valor' => 'x']);

        $informe = $this->auditar();
        $this->assertNotEmpty($this->hallazgos($informe, 'configuracion', 'colegio_configuracion'));
        $this->assertNotEmpty($this->hallazgos($informe, 'configuracion', 'landing_contenido'));
        $unicos = array_column($this->hallazgos($informe, 'restricciones_unicas'), 'detalle', 'tabla');
        foreach (['cursos', 'estudiantes', 'profesores'] as $tabla) {
            $this->assertArrayHasKey($tabla, $unicos, "debe reportar el único global de {$tabla}");
        }
        // R2a (B4, base de pruebas) ya las convirtió en únicos compuestos con id_institucion
        foreach (['materias', 'periodos_academicos', 'tipos_actividad', 'landing_contenido'] as $tabla) {
            $this->assertArrayNotHasKey($tabla, $unicos, "{$tabla} ya tiene único compuesto");
        }
    }

    public function test_el_comando_no_modifica_ningun_dato(): void
    {
        $tablas = ['usuarios', 'cursos', 'materias', 'matriculas', 'detalle_notas', 'periodos_academicos', 'instituciones', 'eventos'];
        $antes = array_map(fn ($t) => $this->huella($t), $tablas);

        $this->artisan('institucion:auditar', ['--plantilla-correspondencia' => 'prueba_plantilla'])->assertSuccessful();

        $this->assertSame($antes, array_map(fn ($t) => $this->huella($t), $tablas));
    }

    public function test_solo_lectura_bloquea_escrituras_en_el_motor(): void
    {
        $antes = $this->huella('materias');
        $bloqueado = false;
        try {
            SoloLectura::ejecutar(DB::connection(), fn () => DB::table('materias')->insert(['nombre_materia' => 'Intrusa', 'intensidad_horaria' => 1, 'estado' => 'activo']));
        } catch (Throwable $e) {
            $bloqueado = true;
        }
        $this->assertTrue($bloqueado, 'la escritura dentro de SoloLectura debe fallar');
        $this->assertSame($antes, $this->huella('materias'));
        DB::table('materias')->insert(['nombre_materia' => 'Posterior', 'intensidad_horaria' => 1, 'estado' => 'activo']);
        $this->assertSame(1, DB::table('materias')->where('nombre_materia', 'Posterior')->count(), 'al salir, la conexión vuelve a escritura normal');
    }

    public function test_plantilla_de_correspondencia_no_toma_decisiones(): void
    {
        MateriaFactory::new()->create(['nombre_materia' => 'Sin uso']);
        $this->artisan('institucion:auditar', ['--plantilla-correspondencia' => 'prueba_plantilla'])->assertSuccessful();

        $plantilla = json_decode(file_get_contents(storage_path('app/auditoria/prueba_plantilla.json')), true);
        @unlink(storage_path('app/auditoria/prueba_plantilla.json'));

        $this->assertSame('correspondencia-institucional/v1', $plantilla['formato']);
        $this->assertNotEmpty($plantilla['decisiones']);
        foreach ($plantilla['decisiones'] as $d) {
            $this->assertNull($d['decision']);
            $this->assertNull($d['institucion_destino']);
            $this->assertSame([], $d['instituciones_clon']);
        }
    }
}
