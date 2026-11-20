<?php

namespace Tests\Feature\Aislamiento;

use App\Core\Seguridad\GuardiaMigracionesEtapa2;
use App\Core\Seguridad\SoloLectura;
use App\Modules\Instituciones\Poblacion\Correspondencia;
use App\Modules\Instituciones\Poblacion\SimuladorPoblacion as Sim;
use Database\Factories\EventoFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * B3 — institucion:poblar --simular con dos colegios y catálogos compartidos
 * (docs/seguridad/etapa2-b3.md). Las escrituras que aparecen aquí son
 * fixtures de la prueba (transacción de RefreshDatabase), nunca del simulador.
 */
class Etapa2SimulacionPoblacionTest extends AislamientoTestCase
{
    private const TABLAS_VERIFICADAS = ['materias', 'periodos_academicos', 'tipos_actividad', 'evento_categorias', 'estudiantes', 'profesores', 'acudientes',
        'eventos', 'soportes', 'asignaciones_academicas', 'actividades', 'boletines', 'matriculas', 'profesor_materia', 'landing_contenido', 'colegio_configuracion', 'migracion_pertenencia'];

    private function plan(?array $columnas = null): array
    {
        return SoloLectura::ejecutar(DB::connection(), fn () => (new Sim(DB::connection(), $columnas))->ejecutar());
    }

    private function registro(array $plan, string $tabla, int $id): array
    {
        foreach ($plan['registros'] as $r) {
            if ($r['tabla'] === $tabla && $r['id'] === $id) {
                return $r;
            }
        }
        $this->fail("No está {$tabla}#{$id} en el plan");
    }

    private function ids(string $c): int
    {
        return $this->e->{$c}['institucion']->id_institucion;
    }

    private function huellas(): array
    {
        return collect(self::TABLAS_VERIFICADAS)->mapWithKeys(fn ($t) => [$t => $this->huella($t)])->all();
    }

    public function test_1_y_2_no_escribe_ni_modifica_relaciones(): void
    {
        $antes = $this->huellas();
        $consultas = [];
        DB::listen(function ($q) use (&$consultas) {
            $consultas[] = strtolower(ltrim($q->sql));
        });

        $this->assertSame(0, Artisan::call('institucion:poblar', ['--simular' => true]));

        $this->assertSame($antes, $this->huellas());
        $escrituras = array_filter($consultas, fn ($sql) => preg_match('/^(insert|update|delete|replace|alter|create|drop|truncate)\b/', $sql));
        $this->assertSame([], array_values($escrituras), 'el simulador solo debe leer');
    }

    public function test_3_detecta_catalogos_compartidos(): void
    {
        $plan = $this->plan();
        $ambas = [$this->ids('a'), $this->ids('b')];
        sort($ambas);
        foreach ([['materias', $this->e->a['materia']->id_materia], ['periodos_academicos', $this->e->a['periodo']->id_periodo], ['tipos_actividad', $this->e->a['tipo']->id_tipo_actividad]] as [$tabla, $id]) {
            $r = $this->registro($plan, $tabla, $id);
            $this->assertSame(Sim::COMPARTIDO, $r['situacion'], $tabla);
            $this->assertSame($ambas, $r['instituciones']);
            $this->assertSame('clonar', $r['accion_propuesta']);
            $this->assertNull($r['institucion_propuesta'], 'no se elige institución en un compartido');
        }
        // Ciencias solo la usa Alfa → asignable sin ambigüedad
        $ciencias = $this->registro($plan, 'materias', $this->e->a['asignacion2']->id_materia);
        $this->assertSame(Sim::INEQUIVOCA, $ciencias['situacion']);
        $this->assertSame($this->ids('a'), $ciencias['institucion_propuesta']);
        // Perfiles: institución de su usuario
        $this->assertSame($this->ids('b'), $this->registro($plan, 'estudiantes', $this->e->b['estudiante']->id_estudiante)['institucion_propuesta']);
    }

    public function test_4_detecta_ambiguos_plantillas_y_no_asigna_por_defecto(): void
    {
        $plan = $this->plan();
        $usada = $this->e->a['evento']->id_categoria;
        foreach (DB::table('evento_categorias')->get() as $c) {
            $r = $this->registro($plan, 'evento_categorias', $c->id_categoria);
            if ($c->es_sistema) {
                $this->assertSame(Sim::PLANTILLA, $r['situacion'], $c->slug);
                $this->assertSame(['ninguna'], $r['opciones']);
            } elseif ($c->id_categoria !== $usada) {
                $this->assertSame(Sim::SIN_PROPIETARIO, $r['situacion'], $c->slug);
                $this->assertNull($r['institucion_propuesta']);
                $this->assertSame(['asignar', 'plantilla', 'clonar', 'pendiente', 'descartar'], $r['opciones']);
            }
        }
        // Configuración y landing: siempre decisión humana, nunca una institución propuesta
        foreach ($plan['registros'] as $r) {
            if (in_array($r['tabla'], ['colegio_configuracion', 'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias'], true)) {
                $this->assertSame(Sim::MANUAL, $r['situacion']);
                $this->assertNull($r['institucion_propuesta']);
            }
        }
        // Evento creado por el SuperAdmin: no se asigna por defecto
        $ev = EventoFactory::new()->create(['id_categoria' => $usada, 'id_usuario_creador' => $this->e->superadmin->id_usuario, 'fecha_inicio' => '2026-11-21', 'fecha_fin' => '2026-11-21']);
        $r = $this->registro($this->plan(), 'eventos', $ev->id_evento);
        $this->assertSame(Sim::MANUAL, $r['situacion']);
        $this->assertSame('dueno_superadmin', $r['regla']);
        $this->assertNull($r['institucion_propuesta']);
    }

    public function test_5_detecta_relaciones_cruzadas(): void
    {
        DB::table('matriculas')->insert(['id_estudiante' => $this->e->b['estudiante']->id_estudiante, 'id_curso' => $this->e->a['curso7a']->id_curso, 'anio_lectivo' => 2025, 'fecha_matricula' => '2025-01-15', 'estado_matricula' => 'activa']);
        $plan = $this->plan();

        $r = $this->registro($plan, 'estudiantes', $this->e->b['estudiante']->id_estudiante);
        $this->assertSame(Sim::INCONSISTENTE, $r['situacion']);
        $this->assertSame('evidencia_de_otra_institucion', $r['regla']);
        $this->assertContains($this->ids('a'), $r['instituciones']);
        $this->assertNotEmpty(array_filter($plan['relaciones_cruzadas'], fn ($h) => $h['tabla'] === 'matriculas'));
    }

    public function test_6_enumera_las_referencias_que_habria_que_repuntar(): void
    {
        $plan = $this->plan();
        $clon = collect($plan['clonaciones'])->first(fn ($c) => $c['tabla'] === 'periodos_academicos' && $c['id'] === $this->e->a['periodo']->id_periodo);
        $this->assertNotNull($clon);
        $this->assertSame(1, $clon['copias_necesarias']);

        $porA = collect($clon['referencias_por_institucion'][$this->ids('a')]['filas']);
        $porB = collect($clon['referencias_por_institucion'][$this->ids('b')]['filas']);
        $this->assertTrue($porA->contains(fn ($f) => $f['tabla'] === 'actividades' && $f['rid'] === (string) $this->e->a['actividad']->id_actividad));
        $this->assertTrue($porA->contains(fn ($f) => $f['tabla'] === 'boletines' && $f['rid'] === (string) $this->e->a['boletin']->id_boletin));
        $this->assertTrue($porB->contains(fn ($f) => $f['tabla'] === 'actividades' && $f['rid'] === (string) $this->e->b['actividad']->id_actividad));
        $this->assertSame($porA->count() + $porB->count() - max($porA->count(), $porB->count()), $clon['referencias_a_repuntar_minimo']);
        // Con R2a (único compuesto) ya no hay conflicto; los de identidad siguen globales
        $this->assertSame([], $clon['conflictos_unicos']);
        $this->assertEmpty(array_filter($clon['riesgos_integridad'], fn ($t) => str_contains($t, 'R2')));
        $this->assertNotEmpty(array_filter($clon['riesgos_integridad'], fn ($t) => str_contains($t, 'CASCADE')));
    }

    public function test_7_resultado_determinista(): void
    {
        $this->assertSame($this->plan()['huella'], $this->plan()['huella']);
    }

    public function test_8_con_columnas_b2_compara_el_valor_actual(): void
    {
        $this->assertTrue($this->plan()['esquema']['b2_aplicada']);
        $ciencias = $this->e->a['asignacion2']->id_materia;
        $estudianteB = $this->e->b['estudiante']->id_estudiante;
        DB::table('materias')->where('id_materia', $ciencias)->update(['id_institucion' => $this->ids('a')]);           // coincide
        DB::table('estudiantes')->where('id_estudiante', $estudianteB)->update(['id_institucion' => $this->ids('a')]);  // contradice

        $plan = $this->plan();
        $this->assertSame(Sim::YA_ASIGNADO, $this->registro($plan, 'materias', $ciencias)['situacion']);
        $r = $this->registro($plan, 'estudiantes', $estudianteB);
        $this->assertSame(Sim::INCONSISTENTE, $r['situacion']);
        $this->assertSame('valor_actual_distinto', $r['regla']);
        $this->assertSame($this->ids('a'), $r['valor_actual']);
    }

    public function test_9_sin_columnas_b2_el_plan_es_el_mismo(): void
    {
        // Estado de B2 recién aplicada: columnas presentes pero sin valores
        foreach (array_keys(Sim::TABLAS) as $t) {
            DB::table($t)->update(['id_institucion' => null]);
        }
        $con = $this->plan();
        $sin = $this->plan([]);
        $this->assertFalse($sin['esquema']['b2_aplicada']);
        unset($con['esquema'], $con['huella'], $sin['esquema'], $sin['huella']);
        $this->assertSame($con, $sin);
    }

    public function test_correspondencia_con_decisiones_vacias_y_campos_requeridos(): void
    {
        $plan = $this->plan();
        $c = Correspondencia::generar($plan, 'prueba');
        $this->assertSame(Correspondencia::FORMATO, $c['formato']);   // v4 desde B10 (archivar_sin_propietario)
        $this->assertSame($plan['huella'], $c['huella_plan']);
        $this->assertNotEmpty($c['decisiones']);
        foreach ($c['decisiones'] as $d) {
            foreach (['tabla', 'id', 'situacion', 'instituciones_candidatas', 'accion_propuesta', 'decision', 'justificacion', 'dependencias'] as $k) {
                $this->assertArrayHasKey($k, $d);
            }
            $this->assertNull($d['decision'], 'el simulador no decide');
            $this->assertNull($d['institucion_destino']);
            $this->assertSame([], $d['instituciones_clon']);
            $this->assertNotContains($d['situacion'], [Sim::INEQUIVOCA, Sim::PLANTILLA, Sim::YA_ASIGNADO]);
        }
        $materia = collect($c['decisiones'])->first(fn ($d) => $d['tabla'] === 'materias' && $d['id'] === $this->e->a['materia']->id_materia);
        $this->assertSame(['colegio-alfa', 'colegio-zeta'], $materia['instituciones_candidatas']);
        $this->assertSame(1, $materia['clonacion']['copias_necesarias']);
    }

    public function test_sin_simular_no_hace_nada(): void
    {
        $antes = $this->huellas();
        $this->assertSame(1, Artisan::call('institucion:poblar'));
        $this->assertSame($antes, $this->huellas());
    }

    public function test_dependencias_del_singleton_excluyen_comentarios_y_al_simulador(): void
    {
        $deps = Sim::dependenciasSingleton();
        $archivos = array_column($deps, 'archivo');
        // B6 eliminó todas las llamadas directas a ColegioConfiguracion::singleton()
        $this->assertSame([], array_values(array_filter($deps, fn ($d) => $d['tipo'] === 'directa')));
        // Las vistas siguen usando $colegioConfiguracion, ahora resuelta por institución
        $this->assertContains('resources/views/layouts/panel.blade.php', $archivos);
        $this->assertEmpty(array_filter($archivos, fn ($a) => str_contains($a, 'Instituciones/Poblacion') || str_contains($a, 'PoblarPertenencia') || str_contains($a, 'PlataformaConfiguracion')));
    }

    /** Inyectar un huérfano exige desactivar FK (no posible dentro de la transacción en SQLite). */
    #[Group('mariadb')]
    public function test_detecta_perfil_huerfano(): void
    {
        Schema::disableForeignKeyConstraints();
        $id = DB::table('acudientes')->insertGetId(['id_usuario' => 999999]);
        Schema::enableForeignKeyConstraints();

        $r = $this->registro($this->plan(), 'acudientes', $id);
        $this->assertSame(Sim::HUERFANO, $r['situacion']);
        $this->assertSame('revisar', $r['accion_propuesta']);
    }

    public function test_guardia_de_migraciones_etapa2(): void
    {
        // Conexión de pruebas: permitida
        GuardiaMigracionesEtapa2::asegurar(DB::connection(), 'm');

        // Una base SQLite en archivo (no autorizada) se bloquea
        $archivo = tempnam(sys_get_temp_dir(), 'guardia').'.sqlite';
        touch($archivo);
        config(['database.connections.guardia_tmp' => ['driver' => 'sqlite', 'database' => $archivo, 'prefix' => '', 'foreign_key_constraints' => true]]);
        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('BLOQUEADO');
            GuardiaMigracionesEtapa2::asegurar(DB::connection('guardia_tmp'), 'm');
        } finally {
            DB::purge('guardia_tmp');
            @unlink($archivo);
        }
    }
}
