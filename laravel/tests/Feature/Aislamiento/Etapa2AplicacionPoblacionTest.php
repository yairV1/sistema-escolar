<?php

namespace Tests\Feature\Aislamiento;

use App\Core\Seguridad\SoloLectura;
use App\Modules\Instituciones\Poblacion\AplicadorPoblacion;
use App\Modules\Instituciones\Poblacion\Correspondencia;
use App\Modules\Instituciones\Poblacion\SimuladorPoblacion as Sim;
use App\Modules\Instituciones\Poblacion\VerificadorPertenencia;
use Database\Factories\ActividadFactory;
use Database\Factories\AsignacionAcademicaFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * B4 — institucion:poblar --aplicar / --revertir con dos colegios ficticios que
 * comparten materia (con área), periodo, tipo de actividad y categoría.
 * Solo base de pruebas (docs/seguridad/etapa2-b4.md).
 */
class Etapa2AplicacionPoblacionTest extends AislamientoTestCase
{
    private const TABLAS = ['materias', 'areas', 'periodos_academicos', 'tipos_actividad', 'evento_categorias', 'escala_notas', 'estudiantes', 'profesores', 'acudientes',
        'eventos', 'soportes', 'colegio_configuracion', 'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias',
        'asignaciones_academicas', 'profesor_materia', 'actividades', 'boletines', 'matriculas', 'detalle_notas', 'asistencia', 'horarios', 'cursos'];

    private int $area;

    protected function setUp(): void
    {
        parent::setUp();
        // Área usada por la materia compartida → también será compartida
        $this->area = DB::table('areas')->insertGetId(['nombre_area' => 'Ciencias Exactas', 'estado' => 'activo'], 'id_area');
        DB::table('materias')->where('id_materia', $this->e->a['materia']->id_materia)->update(['id_area' => $this->area]);
        DB::table('profesor_materia')->insert([
            ['id_profesor' => $this->e->a['profesor']->id_profesor, 'id_materia' => $this->e->a['materia']->id_materia],
            ['id_profesor' => $this->e->b['profesor']->id_profesor, 'id_materia' => $this->e->a['materia']->id_materia],
        ]);
        if (! DB::table('colegio_configuracion')->exists()) {
            DB::table('colegio_configuracion')->insert(['nombre_colegio' => 'Único', 'created_at' => now(), 'updated_at' => now()]);
        }
        $cfg = DB::table('colegio_configuracion')->value('id_configuracion');
        DB::table('colegio_imagenes')->insert(['id_configuracion' => $cfg, 'imagen' => 'x.jpg', 'tipo' => 'fachada', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('landing_galeria')->insert(['imagen' => 'g.jpg', 'orden' => 1, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('landing_noticias')->insert(['titulo' => 'Noticia', 'descripcion' => 'd', 'fecha' => '2026-11-01', 'orden' => 1, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()]);
    }

    // ---------------------------------------------------------------- ayudas
    private function plan(): array
    {
        return SoloLectura::ejecutar(DB::connection(), fn () => (new Sim(DB::connection()))->ejecutar());
    }

    /** Decisiones de prueba (datos ficticios): compartidos → clonar; grupos con acciones conjuntas. */
    private function aprobado(?array $plan = null): array
    {
        $c = Correspondencia::generar($plan ?? $this->plan(), DB::connection()->getDatabaseName());
        foreach ($c['decisiones'] as &$d) {
            if ($d['situacion'] === Sim::COMPARTIDO) {
                $d['decision'] = 'clonar';
                $d['instituciones_clon'] = $d['instituciones_candidatas'];
                $d['conserva_original'] = 'colegio-alfa';
            } elseif ($d['tabla'] === 'eventos') {
                $d['decision'] = 'asignar';
                $d['institucion_destino'] = 'colegio-alfa';
            }
        }
        unset($d);
        $porGrupo = [
            'configuracion' => ['decision' => 'asignar', 'institucion_destino' => 'colegio-alfa'],
            'imagenes' => ['decision' => 'seguir_configuracion'],
            'landing' => ['decision' => 'clonar', 'instituciones_clon' => ['colegio-alfa', 'colegio-zeta'], 'conserva_original' => 'colegio-alfa'],
            'galeria' => ['decision' => 'asignar', 'institucion_destino' => 'colegio-zeta'],
            'noticias' => ['decision' => 'descartar'],
            'categorias_calendario' => ['decision' => 'plantilla'],
        ];
        foreach ($c['grupos'] as &$g) {
            $g = array_merge($g, $porGrupo[$g['grupo']]);
        }
        $c['aprobacion'] = ['ambito' => 'pruebas', 'base' => DB::connection()->getDatabaseName(), 'huella_plan' => $c['huella_plan'], 'aprobado_por' => 'Suite B4 (datos ficticios)', 'fecha' => '2026-11-19T09:00:00-05:00'];

        return $c;
    }

    private function aplicador(): AplicadorPoblacion
    {
        return new AplicadorPoblacion(DB::connection());
    }

    private function huellas(): array
    {
        return collect(self::TABLAS)->mapWithKeys(fn ($t) => [$t => $this->huella($t)])->all();
    }

    private function inst(string $c): int
    {
        return $this->e->{$c}['institucion']->id_institucion;
    }

    private function aplicarOk(?array $archivo = null): array
    {
        $r = $this->aplicador()->aplicar($archivo ?? $this->aprobado());
        $this->assertSame('aplicado', $r['estado'], implode("\n", $r['errores']));

        return $r;
    }

    // --------------------------------------------------------------- pruebas
    public function test_1_aplica_las_decisiones_aprobadas(): void
    {
        $mat = $this->e->a['materia']->id_materia;
        $r = $this->aplicarOk();

        // Materia compartida: original → Alfa; copia → Zeta, con su asignación y profesor_materia repuntados
        $this->assertSame($this->inst('a'), (int) DB::table('materias')->where('id_materia', $mat)->value('id_institucion'));
        $copia = DB::table('materias')->where('nombre_materia', 'Matemáticas')->where('id_institucion', $this->inst('b'))->first();
        $this->assertNotNull($copia);
        $this->assertSame((int) $copia->id_materia, (int) DB::table('asignaciones_academicas')->where('id_asignacion', $this->e->b['asignacion']->id_asignacion)->value('id_materia'));
        $this->assertSame($mat, (int) DB::table('asignaciones_academicas')->where('id_asignacion', $this->e->a['asignacion']->id_asignacion)->value('id_materia'));
        $this->assertTrue(DB::table('profesor_materia')->where('id_profesor', $this->e->b['profesor']->id_profesor)->where('id_materia', $copia->id_materia)->exists());
        // Área compartida: la copia de Zeta de la materia apunta a la copia de Zeta del área
        $areaZeta = DB::table('areas')->where('nombre_area', 'Ciencias Exactas')->where('id_institucion', $this->inst('b'))->value('id_area');
        $this->assertNotNull($areaZeta);
        $this->assertSame((int) $areaZeta, (int) $copia->id_area);
        $this->assertSame($this->area, (int) DB::table('materias')->where('id_materia', $mat)->value('id_area'));
        // Periodo y tipo: actividades y boletines de Zeta → copias de Zeta
        $periodoZeta = DB::table('periodos_academicos')->where('id_institucion', $this->inst('b'))->value('id_periodo');
        $this->assertSame((int) $periodoZeta, (int) DB::table('actividades')->where('id_actividad', $this->e->b['actividad']->id_actividad)->value('id_periodo'));
        $this->assertSame((int) $periodoZeta, (int) DB::table('boletines')->where('id_boletin', $this->e->b['boletin']->id_boletin)->value('id_periodo'));
        // Categoría compartida: evento de Zeta → copia de Zeta
        $catZeta = DB::table('evento_categorias')->where('slug', 'eventos-institucionales')->where('id_institucion', $this->inst('b'))->value('id_categoria');
        $this->assertSame((int) $catZeta, (int) DB::table('eventos')->where('id_evento', $this->e->b['evento']->id_evento)->value('id_categoria'));
        // Automáticas: perfiles por usuario; Ciencias solo de Alfa
        $this->assertSame($this->inst('b'), (int) DB::table('estudiantes')->where('id_estudiante', $this->e->b['estudiante']->id_estudiante)->value('id_institucion'));
        $this->assertSame($this->inst('a'), (int) DB::table('materias')->where('id_materia', $this->e->a['asignacion2']->id_materia)->value('id_institucion'));
        // Grupos: configuración + imágenes → Alfa; landing clonada completa para ambos; galería → Zeta; noticias descartadas; categorías sin uso → plantilla
        $this->assertSame([$this->inst('a')], DB::table('colegio_imagenes')->distinct()->pluck('id_institucion')->map(fn ($i) => (int) $i)->all());
        $claves = DB::table('landing_contenido')->whereNull('id_institucion')->count();
        $this->assertSame(0, $claves);
        $this->assertSame(DB::table('landing_contenido')->where('id_institucion', $this->inst('a'))->pluck('clave')->sort()->values()->all(),
            DB::table('landing_contenido')->where('id_institucion', $this->inst('b'))->pluck('clave')->sort()->values()->all());
        $this->assertSame($this->inst('b'), (int) DB::table('landing_galeria')->value('id_institucion'));
        $this->assertNull(DB::table('landing_noticias')->value('id_institucion'));
        $this->assertSame(0, DB::table('evento_categorias')->where('es_sistema', 0)->whereNotIn('slug', ['eventos-institucionales'])->whereNotNull('id_institucion')->count());
        // Trazabilidad
        $this->assertGreaterThan(0, $r['operaciones']['clonar']);
        $this->assertGreaterThan(0, $r['operaciones']['repuntar']);
        $this->assertSame([], (new VerificadorPertenencia(DB::connection()))->violaciones());
    }

    public function test_2_rechaza_decisiones_incompletas_o_invalidas_sin_tocar_nada(): void
    {
        $antes = $this->huellas();
        $casos = [
            'sin decisión' => fn ($c) => $this->sinGrupo($c, 'categorias_calendario'),
            'pendiente' => fn ($c) => $this->conGrupo($c, 'noticias', ['decision' => 'pendiente']),
            'asignar un compartido' => fn ($c) => $this->conDecision($c, 'materias', $this->e->a['materia']->id_materia, ['decision' => 'asignar', 'institucion_destino' => 'colegio-alfa']),
            'clonar sin conserva_original' => fn ($c) => $this->conDecision($c, 'periodos_academicos', $this->e->a['periodo']->id_periodo, ['conserva_original' => null]),
            'clonar sin cubrir a un usuario' => fn ($c) => $this->conDecision($c, 'tipos_actividad', $this->e->a['tipo']->id_tipo_actividad, ['instituciones_clon' => ['colegio-alfa']]),
            'plantilla en landing' => fn ($c) => $this->conGrupo($c, 'landing', ['decision' => 'plantilla']),
            'slug inexistente' => fn ($c) => $this->conGrupo($c, 'galeria', ['institucion_destino' => 'colegio-inexistente']),
            'otra base' => fn ($c) => array_merge($c, ['base' => 'colegio']),
            'sin aprobación' => fn ($c) => array_merge($c, ['aprobacion' => null]),
            'aprobación de otro ámbito' => fn ($c) => array_merge($c, ['aprobacion' => ['ambito' => 'produccion'] + $c['aprobacion']]),
            'aprobación de otro plan' => fn ($c) => array_merge($c, ['aprobacion' => ['huella_plan' => str_repeat('0', 64)] + $c['aprobacion']]),
        ];
        foreach ($casos as $nombre => $alterar) {
            $r = $this->aplicador()->aplicar($alterar($this->aprobado()));
            $this->assertSame('rechazado', $r['estado'], $nombre);
            $this->assertNotEmpty($r['errores'], $nombre);
            $this->assertSame($antes, $this->huellas(), "{$nombre}: no debe cambiar nada");
            $this->assertSame(0, DB::table('migracion_pertenencia')->count(), $nombre);
        }
    }

    public function test_3_rechaza_huella_desactualizada(): void
    {
        $archivo = $this->aprobado();
        // Cambio de datos posterior a la aprobación: Zeta empieza a usar «Ciencias»
        ActividadFactory::new()->create(['id_asignacion' => $this->e->b['asignacion']->id_asignacion, 'id_periodo' => $this->e->a['periodo']->id_periodo, 'id_tipo_actividad' => $this->e->a['tipo']->id_tipo_actividad]);
        AsignacionAcademicaFactory::new()->create(['id_profesor' => $this->e->b['profesor']->id_profesor, 'id_materia' => $this->e->a['asignacion2']->id_materia, 'id_curso' => $this->e->b['curso']->id_curso]);
        $antes = $this->huellas();

        $r = $this->aplicador()->aplicar($archivo);
        $this->assertSame('rechazado', $r['estado']);
        $this->assertStringContainsString('Huella desactualizada', $r['errores'][0]);
        $this->assertSame($antes, $this->huellas());
    }

    /** La huella se vuelve a comprobar DENTRO de la operación protegida, no solo al inicio. */
    public function test_3b_cambio_concurrente_tras_la_validacion_previa_se_rechaza(): void
    {
        $ap = $this->aplicador();
        $ap->antesDeBloquear = function () {
            // Otro proceso cambia datos relevantes entre la validación previa y el bloqueo
            ActividadFactory::new()->create(['id_asignacion' => $this->e->b['asignacion2'] ?? $this->e->b['asignacion']->id_asignacion, 'id_periodo' => $this->e->a['periodo']->id_periodo, 'id_tipo_actividad' => $this->e->a['tipo']->id_tipo_actividad]);
            AsignacionAcademicaFactory::new()->create(['id_profesor' => $this->e->b['profesor']->id_profesor, 'id_materia' => $this->e->a['asignacion2']->id_materia, 'id_curso' => $this->e->b['curso']->id_curso, 'anio_lectivo' => 2019]);
        };
        $archivo = $this->aprobado();
        $filas = collect(self::TABLAS)->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()]);
        $conInstitucion = collect(array_keys(Sim::TABLAS))->mapWithKeys(fn ($t) => [$t => DB::table($t)->whereNotNull('id_institucion')->count()]);
        $r = $ap->aplicar($archivo);
        $this->assertSame('rechazado', $r['estado']);
        $this->assertStringContainsString('Huella desactualizada', $r['errores'][0]);
        $this->assertSame(0, DB::table('migracion_pertenencia')->count(), 'no se escribió trazabilidad');
        foreach (array_keys(Sim::TABLAS) as $t) {
            $this->assertSame($conInstitucion[$t], DB::table($t)->whereNotNull('id_institucion')->count(), "{$t}: no se asignó nada");
        }
        // solo cambian las 2 filas que insertó el «otro proceso»; ninguna copia
        $this->assertSame($filas['materias'], DB::table('materias')->count());
        $this->assertSame($filas['actividades'] + 1, DB::table('actividades')->count());
        $this->assertSame($filas['asignaciones_academicas'] + 1, DB::table('asignaciones_academicas')->count());
    }

    public function test_4_y_9_clonacion_sin_perdida_de_referencias_e_integridad_fk(): void
    {
        $refs = fn () => [
            DB::table('asignaciones_academicas')->count(), DB::table('profesor_materia')->count(), DB::table('actividades')->count(),
            DB::table('boletines')->count(), DB::table('eventos')->count(), DB::table('detalle_notas')->count(), DB::table('asistencia')->count(),
        ];
        $antes = $refs();
        $this->aplicarOk();
        $this->assertSame($antes, $refs(), 'ninguna fila referenciante se pierde ni se duplica');

        $audit = (new \App\Modules\Instituciones\Auditoria\AuditoriaPertenencia(DB::connection()))->ejecutar()['hallazgos'];
        $this->assertSame([], array_values(array_filter($audit, fn ($h) => in_array($h['seccion'], ['referencias_huerfanas', 'relaciones_cruzadas'], true))));
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
        }
    }

    public function test_5_aislamiento_entre_colegios(): void
    {
        $this->aplicarOk();
        foreach (['a' => 'b', 'b' => 'a'] as $propio => $ajeno) {
            $id = $this->inst($propio);
            // Todo lo que usan los cursos de un colegio es de ese colegio
            $ajenos = DB::table('asignaciones_academicas as a')->join('cursos as c', 'c.id_curso', '=', 'a.id_curso')->join('materias as m', 'm.id_materia', '=', 'a.id_materia')
                ->where('c.id_institucion', $id)->where('m.id_institucion', '<>', $id)->count();
            $this->assertSame(0, $ajenos);
            $ajenos = DB::table('actividades as ac')->join('asignaciones_academicas as a', 'a.id_asignacion', '=', 'ac.id_asignacion')->join('cursos as c', 'c.id_curso', '=', 'a.id_curso')
                ->join('periodos_academicos as p', 'p.id_periodo', '=', 'ac.id_periodo')->join('tipos_actividad as t', 't.id_tipo_actividad', '=', 'ac.id_tipo_actividad')
                ->where('c.id_institucion', $id)->where(fn ($q) => $q->where('p.id_institucion', '<>', $id)->orWhere('t.id_institucion', '<>', $id))->count();
            $this->assertSame(0, $ajenos);
        }
        // Cada colegio ve su propio «Matemáticas» y no el del otro
        $this->assertSame(1, DB::table('materias')->where('nombre_materia', 'Matemáticas')->where('id_institucion', $this->inst('a'))->count());
        $this->assertSame(1, DB::table('materias')->where('nombre_materia', 'Matemáticas')->where('id_institucion', $this->inst('b'))->count());
    }

    public function test_6_conserva_los_datos_originales(): void
    {
        $sinInst = fn ($t, $pk) => md5(json_encode(DB::table($t)->orderBy($pk)->get()->map(function ($f) {
            unset($f->id_institucion);

            return $f;
        })));
        $originales = ['materias' => 'id_materia', 'periodos_academicos' => 'id_periodo', 'tipos_actividad' => 'id_tipo_actividad', 'landing_contenido' => 'id_contenido', 'evento_categorias' => 'id_categoria'];
        $ids = collect($originales)->map(fn ($pk, $t) => DB::table($t)->pluck($pk)->all())->all();
        $antes = collect($originales)->map(fn ($pk, $t) => $sinInst($t, $pk))->all();

        $this->aplicarOk();

        foreach ($originales as $t => $pk) {
            $this->assertSame(count($ids[$t]), DB::table($t)->whereIn($pk, $ids[$t])->count(), "{$t}: no se borra ningún original");
            $despues = md5(json_encode(DB::table($t)->whereIn($pk, $ids[$t])->orderBy($pk)->get()->map(function ($f) {
                unset($f->id_institucion);

                return $f;
            })));
            // Solo cambian id_institucion y, en materias, el área/padre de las copias (no de los originales)
            $this->assertSame($antes[$t], $despues, "{$t}: los originales solo cambian en id_institucion");
        }
    }

    public function test_7_una_falla_a_mitad_revierte_toda_la_transaccion(): void
    {
        $antes = $this->huellas();
        foreach (['repuntar', 'plantilla'] as $accion) {
            $ap = $this->aplicador();
            $ap->despuesDeCadaPaso = function ($n, $a) use ($accion) {
                if ($a === $accion) {
                    throw new RuntimeException("interrupción simulada en {$a}");
                }
            };
            try {
                $ap->aplicar($this->aprobado());
                $this->fail('debió interrumpirse');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('interrupción simulada', $e->getMessage());
            }
            $this->assertSame($antes, $this->huellas(), "tras fallar en {$accion} todo vuelve al estado previo");
            $this->assertSame(0, DB::table('migracion_pertenencia')->count());
        }
        // Y una ejecución completa posterior funciona (recuperación)
        $this->aplicarOk();
    }

    public function test_8_idempotencia(): void
    {
        $archivo = $this->aprobado();
        $r1 = $this->aplicarOk($archivo);
        $despues = $this->huellas();
        $traza = DB::table('migracion_pertenencia')->count();

        $r2 = $this->aplicador()->aplicar($archivo);
        $this->assertSame('ya_aplicado', $r2['estado']);
        $this->assertSame($r1['lote'], $r2['lote']);
        $this->assertSame($despues, $this->huellas());
        $this->assertSame($traza, DB::table('migracion_pertenencia')->count());

        // Una nueva simulación ya no encuentra nada que asignar ni clonar
        $t = $this->plan()['totales'];
        $this->assertSame(0, $t['asignables_automaticamente']);
        $this->assertSame(0, $t['a_clonar']);
        $this->assertSame(0, $t['decisiones_manuales'], 'lo decidido (incluidas plantillas y descartes) ya no pide decisión');
        $this->assertSame(0, $t['inconsistentes']);
    }

    public function test_10_bloquea_la_aplicacion_fuera_de_la_base_de_pruebas(): void
    {
        $archivo = tempnam(sys_get_temp_dir(), 'b4').'.sqlite';
        touch($archivo);
        config(['database.connections.no_autorizada' => ['driver' => 'sqlite', 'database' => $archivo, 'prefix' => '', 'foreign_key_constraints' => true]]);
        try {
            foreach (['aplicar' => fn ($ap) => $ap->aplicar($this->aprobado()), 'revertir' => fn ($ap) => $ap->revertir('x')] as $op => $f) {
                try {
                    $f(new AplicadorPoblacion(DB::connection('no_autorizada')));
                    $this->fail("{$op} debió bloquearse");
                } catch (RuntimeException $e) {
                    $this->assertStringContainsString('BLOQUEADO', $e->getMessage());
                }
            }
            $this->assertSame([], DB::connection('no_autorizada')->select("SELECT name FROM sqlite_master WHERE type = 'table'"), 'no se tocó la base no autorizada');
        } finally {
            DB::purge('no_autorizada');
            @unlink($archivo);
        }
    }

    public function test_revertir_restaura_el_estado_previo_y_respeta_referencias_nuevas(): void
    {
        $plan = $this->plan();
        $antes = $this->huellas();
        $r = $this->aplicarOk($this->aprobado($plan));

        // Una referencia nueva a una copia impide revertir (y no cambia nada)
        $copia = DB::table('materias')->where('nombre_materia', 'Matemáticas')->where('id_institucion', $this->inst('b'))->value('id_materia');
        $idAsig = AsignacionAcademicaFactory::new()->create(['id_profesor' => $this->e->b['profesor']->id_profesor, 'id_materia' => $copia, 'id_curso' => $this->e->b['curso']->id_curso, 'anio_lectivo' => 2019])->id_asignacion;
        $estado = $this->huellas();
        try {
            $this->aplicador()->revertir($r['lote']);
            $this->fail('debió negarse');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('tiene referencias', $e->getMessage());
        }
        $this->assertSame($estado, $this->huellas());
        DB::table('asignaciones_academicas')->where('id_asignacion', $idAsig)->delete();

        $this->assertSame('revertido', $this->aplicador()->revertir($r['lote'])['estado']);
        $this->assertSame($antes, $this->huellas(), 'la reversión deja todo como estaba');
        $this->assertSame($plan['huella'], $this->plan()['huella'], 'la simulación vuelve a dar la misma huella');
        $this->assertSame('nada_que_revertir', $this->aplicador()->revertir($r['lote'])['estado']);
        // Tras revertir, el mismo archivo se puede volver a aplicar
        $this->aplicarOk($this->aprobado($plan));
    }

    public function test_decisiones_por_grupo_con_excepcion_individual(): void
    {
        $c = $this->aprobado();
        foreach ($c['decisiones'] as &$d) {
            if ($d['tabla'] === 'landing_contenido' && $d['descripcion'] === 'hero_imagen') {
                $d['decision'] = 'descartar';
            }
        }
        unset($d);
        $this->aplicarOk($c);
        $this->assertNull(DB::table('landing_contenido')->where('clave', 'hero_imagen')->value('id_institucion'));
        $this->assertSame(1, DB::table('landing_contenido')->where('clave', 'hero_imagen')->count(), 'la clave descartada no se clona');
        $this->assertSame(2, DB::table('landing_contenido')->where('clave', 'hero_titulo')->count(), 'el resto del grupo sí');
    }

    public function test_curso_compartido_entre_colegios_bloquea_la_aplicacion(): void
    {
        // Estudiante de Zeta matriculado en un curso de Alfa
        DB::table('matriculas')->insert(['id_estudiante' => $this->e->b['estudiante']->id_estudiante, 'id_curso' => $this->e->a['curso']->id_curso, 'anio_lectivo' => 2019, 'fecha_matricula' => '2019-01-15', 'estado_matricula' => 'activa']);
        $antes = $this->huellas();
        $r = $this->aplicador()->aplicar($this->aprobado());
        $this->assertSame('rechazado', $r['estado']);
        $this->assertNotEmpty(array_filter($r['errores'], fn ($e) => str_contains($e, 'relacion_inconsistente') || str_contains($e, 'entre colegios')));
        $this->assertSame($antes, $this->huellas());
    }

    public function test_auditoria_tras_aplicar_sin_hallazgos_criticos(): void
    {
        $this->aplicarOk();
        $informe = (new \App\Modules\Instituciones\Auditoria\AuditoriaPertenencia(DB::connection()))->ejecutar();
        $criticos = array_values(array_filter($informe['hallazgos'], fn ($h) => $h['severidad'] === 'critico'));
        $this->assertSame([], $criticos);
        $clases = array_count_values(array_column($informe['catalogos'], 'clase'));
        $this->assertArrayNotHasKey('compartido', $clases);
        $this->assertArrayNotHasKey('sin_uso_ambiguo', $clases, 'las categorías decididas como plantilla ya no son ambiguas');
        // Zeta todavía no tiene configuración propia (B6): advertencia, no crítico
        $this->assertNotEmpty(array_filter($informe['hallazgos'], fn ($h) => $h['tabla'] === 'instituciones' && $h['id'] == $this->inst('b') && str_contains($h['detalle'], 'sin configuración propia')));
    }

    /** Con otra aplicación en curso (cerrojo tomado por otra conexión), no se modifica nada. */
    #[\PHPUnit\Framework\Attributes\Group('mariadb')]
    public function test_cerrojo_impide_dos_aplicaciones_simultaneas(): void
    {
        $base = DB::connection()->getDatabaseName();
        config(['database.connections.segunda' => config('database.connections.'.DB::getDefaultConnection())]);
        $otra = DB::connection('segunda');
        $this->assertSame(1, (int) $otra->selectOne('SELECT GET_LOCK(?, 0) AS c', ["institucion_poblar:{$base}"])->c);
        $antes = $this->huellas();
        try {
            $this->aplicador()->aplicar($this->aprobado());
            $this->fail('debió negarse');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('en curso', $e->getMessage());
        } finally {
            $otra->selectOne('SELECT RELEASE_LOCK(?) AS r', ["institucion_poblar:{$base}"]);
            DB::purge('segunda');
        }
        $this->assertSame($antes, $this->huellas());
        $this->aplicarOk();   // liberado el cerrojo, la aplicación funciona
    }

    /** Regresión: en MariaDB, START TRANSACTION dentro de una transacción abierta la confirmaba implícitamente. */
    public function test_solo_lectura_no_confirma_una_transaccion_exterior(): void
    {
        DB::beginTransaction();
        DB::table('areas')->insert(['nombre_area' => 'Pendiente de confirmar', 'estado' => 'activo']);
        $nivel = DB::transactionLevel();
        SoloLectura::ejecutar(DB::connection(), fn () => DB::table('areas')->count());
        try {
            SoloLectura::ejecutar(DB::connection(), fn () => DB::table('areas')->insert(['nombre_area' => 'Intrusa', 'estado' => 'activo']));
            $this->fail('la escritura debió bloquearse');
        } catch (\Throwable) {
        }
        $this->assertSame($nivel, DB::transactionLevel());
        DB::rollBack();
        $this->assertSame(0, DB::table('areas')->whereIn('nombre_area', ['Pendiente de confirmar', 'Intrusa'])->count(), 'el rollback exterior debe deshacer la escritura previa');
    }

    public function test_clonar_exige_el_unico_compuesto(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Requiere DDL dentro de la transacción de la prueba (solo SQLite).');
        }
        Schema::table('tipos_actividad', fn ($t) => $t->unique('nombre_tipo', 'tipos_actividad_nombre_tipo_unique'));
        $r = $this->aplicador()->aplicar($this->aprobado());
        $this->assertSame('rechazado', $r['estado']);
        $this->assertNotEmpty(array_filter($r['errores'], fn ($e) => str_contains($e, 'R2 anticipado')));
    }

    // ------------------------------------------------------- alteraciones
    private function sinGrupo(array $c, string $grupo): array
    {
        foreach ($c['grupos'] as &$g) {
            if ($g['grupo'] === $grupo) {
                $g['decision'] = null;
            }
        }

        return $c;
    }

    private function conGrupo(array $c, string $grupo, array $cambios): array
    {
        foreach ($c['grupos'] as &$g) {
            if ($g['grupo'] === $grupo) {
                $g = array_merge($g, $cambios);
            }
        }

        return $c;
    }

    private function conDecision(array $c, string $tabla, int $id, array $cambios): array
    {
        foreach ($c['decisiones'] as &$d) {
            if ($d['tabla'] === $tabla && $d['id'] === $id) {
                $d = array_merge($d, $cambios);
            }
        }

        return $c;
    }
}
