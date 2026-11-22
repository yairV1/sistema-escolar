<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Calendario\Models\EventoCategoria;
use App\Modules\Calificaciones\Models\Periodo;
use App\Modules\Calificaciones\Models\TipoActividad;
use App\Modules\GestionAcademica\Models\Materia;
use App\Modules\Usuarios\Models\Estudiante;
use Database\Factories\EstudianteFactory;
use Database\Factories\EventoFactory;
use Database\Factories\UsuarioFactory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Etapa 2 · B5 — catálogos institucionales en ESQUEMA EXPANDIDO (B2 aplicada),
 * por HTTP y a nivel de modelo, con dos colegios ficticios (Alfa / Zeta).
 * El escenario crea sus catálogos sin sesión → quedan sin institución
 * (compartidos, «pendientes de poblar»), como tras aplicar B2 sin poblar.
 */
class Etapa2B5CatalogosTest extends AislamientoTestCase
{
    private function a()
    {
        return $this->e->a['directivo'];
    }

    private function b()
    {
        return $this->e->b['directivo'];
    }

    private function inst(string $c): int
    {
        return $this->e->{$c}['institucion']->id_institucion;
    }

    private function crearMateria($actor, string $nombre, array $extra = [])
    {
        return $this->actingAs($actor)->postJson(route('gestion-academica.materias.store'), ['nombre_materia' => $nombre, 'intensidad_horaria' => 3] + $extra);
    }

    // ------------------------------------------------------------ creación
    public function test_crear_asigna_la_institucion_de_la_sesion_e_ignora_la_del_formulario(): void
    {
        $this->crearMateria($this->a(), 'Química', ['id_institucion' => $this->inst('b')])->assertOk();
        $this->assertSame($this->inst('a'), (int) DB::table('materias')->where('nombre_materia', 'Química')->value('id_institucion'));

        $this->actingAs($this->a())->postJson(route('calificaciones.periodos.store'), ['nombre_periodo' => 'P1 Alfa', 'fecha_inicio' => '2026-01-10', 'fecha_fin' => '2026-03-30', 'anio_lectivo' => 2026, 'id_institucion' => $this->inst('b')])->assertOk();
        $this->assertSame($this->inst('a'), (int) DB::table('periodos_academicos')->where('nombre_periodo', 'P1 Alfa')->value('id_institucion'));

        $this->actingAs($this->b())->postJson(route('calificaciones.tipos-actividad.store'), ['nombre_tipo' => 'Taller Zeta', 'id_institucion' => $this->inst('a')])->assertOk();
        $this->assertSame($this->inst('b'), (int) DB::table('tipos_actividad')->where('nombre_tipo', 'Taller Zeta')->value('id_institucion'));

        $this->actingAs($this->b())->postJson(route('calendario.categorias.store'), ['nombre' => 'Ferias Zeta', 'color' => 'var(--cat-1)', 'icono' => 'bi-star', 'id_institucion' => $this->inst('a')])->assertOk();
        $this->assertSame($this->inst('b'), (int) DB::table('evento_categorias')->where('nombre', 'Ferias Zeta')->value('id_institucion'));
    }

    public function test_asignacion_masiva_no_permite_fijar_la_institucion(): void
    {
        $this->actingAs($this->a());
        $m = Materia::create(['nombre_materia' => 'Masiva', 'intensidad_horaria' => 1, 'id_institucion' => $this->inst('b')]);
        $this->assertSame($this->inst('a'), (int) $m->fresh()->id_institucion, 'con sesión manda la sesión');
        $this->assertNotContains('id_institucion', (new Materia)->getFillable());
        $this->assertNotContains('id_institucion', (new Periodo)->getFillable());
        $this->assertNotContains('id_institucion', (new TipoActividad)->getFillable());
        $this->assertNotContains('id_institucion', (new EventoCategoria)->getFillable());
    }

    public function test_sin_institucion_activa_no_se_crea_nada(): void
    {
        $this->actingAs($this->e->superadmin);
        $this->expectException(RuntimeException::class);
        Materia::create(['nombre_materia' => 'Huérfana', 'intensidad_horaria' => 1]);
    }

    // ------------------------------------------------------- lectura / aislamiento
    public function test_cada_colegio_solo_ve_lo_propio_y_lo_compartido(): void
    {
        $this->crearMateria($this->a(), 'Robótica Alfa')->assertOk();
        $this->crearMateria($this->b(), 'Teatro Zeta')->assertOk();

        $this->actingAs($this->a())->get(route('gestion-academica.materias.index'))->assertOk()
            ->assertSee('Robótica Alfa')->assertDontSee('Teatro Zeta')->assertSee('Matemáticas');
        $this->actingAs($this->b())->get(route('gestion-academica.materias.index'))->assertOk()
            ->assertSee('Teatro Zeta')->assertDontSee('Robótica Alfa');

        $this->actingAs($this->a());
        $this->assertSame(['Ciencias', 'Matemáticas', 'Robótica Alfa'], Materia::deInstitucion()->orderBy('nombre_materia')->pluck('nombre_materia')->all());
    }

    // ------------------------------------------------------- modificación
    public function test_modificar_o_desactivar_lo_ajeno_da_404_y_lo_propio_funciona(): void
    {
        $this->crearMateria($this->a(), 'Ajedrez')->assertOk();
        $id = Materia::where('nombre_materia', 'Ajedrez')->value('id_materia');

        $this->actingAs($this->b())->postJson(route('gestion-academica.materias.update', $id), ['nombre_materia' => 'Robada', 'intensidad_horaria' => 1])->assertNotFound();
        $this->actingAs($this->b())->postJson(route('gestion-academica.materias.desactivar', $id))->assertNotFound();
        $this->assertSame('Ajedrez', Materia::find($id)->nombre_materia);
        $this->assertSame('activo', Materia::find($id)->estado);

        $this->actingAs($this->a())->postJson(route('gestion-academica.materias.update', $id), ['nombre_materia' => 'Ajedrez II', 'intensidad_horaria' => 2])->assertOk();
        $this->actingAs($this->a())->postJson(route('gestion-academica.materias.desactivar', $id))->assertOk();
        $this->assertSame('inactivo', Materia::find($id)->estado);
    }

    public function test_lo_compartido_aun_sin_poblar_es_de_solo_lectura(): void
    {
        $compartida = $this->e->a['materia'];   // creada sin sesión: id_institucion NULL
        $this->assertNull($compartida->fresh()->id_institucion);
        $this->actingAs($this->a())->postJson(route('gestion-academica.materias.update', $compartida->id_materia), ['nombre_materia' => 'Cambiada', 'intensidad_horaria' => 1])
            ->assertStatus(409)->assertJsonPath('success', false);
        $this->actingAs($this->b())->postJson(route('calificaciones.periodos.desactivar', $this->e->a['periodo']->id_periodo))->assertStatus(409);
        $this->assertSame('Matemáticas', $compartida->fresh()->nombre_materia);
    }

    public function test_las_categorias_plantilla_son_de_solo_lectura(): void
    {
        $sistema = EventoCategoria::where('es_sistema', true)->firstOrFail();
        $global = EventoCategoria::where('es_sistema', false)->whereNull('id_institucion')->firstOrFail();
        foreach ([$sistema, $global] as $c) {
            $this->actingAs($this->a())->postJson(route('calendario.categorias.update', $c->id_categoria), ['nombre' => 'Mía', 'color' => 'var(--cat-1)', 'icono' => 'bi-star'])->assertForbidden();
            $this->actingAs($this->a())->postJson(route('calendario.categorias.desactivar', $c->id_categoria))->assertForbidden();
            $this->assertNotSame('Mía', $c->fresh()->nombre);
        }
        // …pero siguen visibles para ambos colegios
        $this->actingAs($this->b());
        $this->assertTrue(EventoCategoria::deInstitucion()->whereKey($global->id_categoria)->exists());
    }

    // ---------------------------------------------- identificadores manipulados
    public function test_ids_de_catalogos_de_otro_colegio_se_rechazan_con_422(): void
    {
        $this->crearMateria($this->b(), 'Solo Zeta')->assertOk();
        $materiaB = Materia::where('nombre_materia', 'Solo Zeta')->value('id_materia');
        $this->actingAs($this->b())->postJson(route('calificaciones.periodos.store'), ['nombre_periodo' => 'Periodo Zeta', 'fecha_inicio' => '2026-01-10', 'fecha_fin' => '2026-03-30', 'anio_lectivo' => 2026])->assertOk();
        $periodoB = Periodo::where('nombre_periodo', 'Periodo Zeta')->value('id_periodo');

        $this->actingAs($this->a())->postJson(route('gestion-academica.asignaciones.store'), [
            'id_profesor' => $this->e->a['profesor']->id_profesor, 'id_materia' => $materiaB, 'id_curso' => $this->e->a['curso7a']->id_curso, 'anio_lectivo' => 2026,
        ])->assertStatus(422)->assertJsonValidationErrors('id_materia');

        $this->actingAs($this->a())->postJson(route('calificaciones.actividades.store', $this->e->a['asignacion']->id_asignacion), [
            'id_periodo' => $periodoB, 'id_tipo_actividad' => $this->e->a['tipo']->id_tipo_actividad, 'titulo' => 'X', 'porcentaje' => 10,
        ])->assertStatus(422)->assertJsonValidationErrors('id_periodo');

        $this->actingAs($this->a())->postJson(route('gestion-academica.materias.store'), ['nombre_materia' => 'Hija', 'intensidad_horaria' => 1, 'id_materia_padre' => $materiaB])
            ->assertStatus(422)->assertJsonValidationErrors('id_materia_padre');

        $this->actingAs($this->a())->postJson(route('registro.docentes.store'), [
            'primer_nombre' => 'Nuevo', 'primer_apellido' => 'Docente', 'tipo_documento' => 'CC', 'numero_documento' => '99887766', 'correo' => 'nuevo.docente@example.test', 'materias' => [$materiaB],
        ])->assertStatus(422)->assertJsonValidationErrors('materias.0');
    }

    // ---------------------------------------------------------------- unicidad
    public function test_unicidad_por_colegio(): void
    {
        $this->crearMateria($this->a(), 'Biología')->assertOk();
        $this->crearMateria($this->a(), 'Biología')->assertStatus(422)->assertJsonValidationErrors('nombre_materia');
        $this->crearMateria($this->b(), 'Biología')->assertOk();   // otro colegio: permitido (R2a)
        $this->crearMateria($this->a(), 'Matemáticas')->assertStatus(422);   // choca con la compartida visible

        $p = ['nombre_periodo' => 'Único', 'fecha_inicio' => '2026-01-10', 'fecha_fin' => '2026-03-30', 'anio_lectivo' => 2026];
        $this->actingAs($this->a())->postJson(route('calificaciones.periodos.store'), $p)->assertOk();
        $this->actingAs($this->a())->postJson(route('calificaciones.periodos.store'), $p)->assertStatus(422)->assertJsonValidationErrors('nombre_periodo');
        $this->actingAs($this->a())->postJson(route('calificaciones.periodos.store'), ['anio_lectivo' => 2027, 'fecha_inicio' => '2027-01-10', 'fecha_fin' => '2027-03-30'] + $p)->assertOk();

        // Editar sin cambiar el nombre no choca consigo mismo
        $id = Materia::where('nombre_materia', 'Biología')->where('id_institucion', $this->inst('a'))->value('id_materia');
        $this->actingAs($this->a())->postJson(route('gestion-academica.materias.update', $id), ['nombre_materia' => 'Biología', 'intensidad_horaria' => 5])->assertOk();
    }

    // -------------------------------------------------------------- SuperAdmin
    public function test_superadmin_sin_acceso_academico_a_catalogos(): void
    {
        $sa = $this->e->superadmin;
        $this->actingAs($sa)->get(route('gestion-academica.materias.index'))->assertForbidden();
        $this->actingAs($sa)->postJson(route('gestion-academica.materias.store'), ['nombre_materia' => 'SA', 'intensidad_horaria' => 1])->assertForbidden();
        $this->actingAs($sa)->get(route('calificaciones.index'))->assertForbidden();
        $this->actingAs($sa)->postJson(route('calendario.categorias.store'), ['nombre' => 'SA', 'color' => 'var(--cat-1)', 'icono' => 'bi-star'])->assertForbidden();
        $this->assertFalse(Materia::where('nombre_materia', 'SA')->exists());
        $this->actingAs($sa);
        $this->assertSame(0, Materia::deInstitucion()->count(), 'sin institución no ve catálogos');
    }

    // ---------------------------------------- perfiles, eventos y soportes
    public function test_perfiles_eventos_y_soportes_copian_la_institucion_del_dueno(): void
    {
        $this->assertSame($this->inst('b'), (int) $this->e->b['estudiante']->fresh()->id_institucion);
        $this->assertSame($this->inst('a'), (int) $this->e->a['evento']->fresh()->id_institucion);
        $this->assertSame($this->inst('b'), (int) $this->e->b['soporte']->fresh()->id_institucion);

        // Con sesión de Alfa no se puede crear el perfil de un usuario de Zeta
        $usuarioZeta = UsuarioFactory::new()->create(['id_rol' => 6, 'id_institucion' => $this->inst('b')]);
        $this->actingAs($this->a());
        try {
            EstudianteFactory::new()->create(['id_usuario' => $usuarioZeta->id_usuario]);
            $this->fail('debió rechazarse');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('otra institución', $e->getMessage());
        }
        $this->assertFalse(Estudiante::where('id_usuario', $usuarioZeta->id_usuario)->exists());

        // El SuperAdmin (sin institución) no recibe una por defecto
        $ev = EventoFactory::new()->create(['id_categoria' => $this->e->a['evento']->id_categoria, 'id_usuario_creador' => $this->e->superadmin->id_usuario, 'fecha_inicio' => '2026-11-25', 'fecha_fin' => '2026-11-25']);
        $this->assertNull($ev->fresh()->id_institucion);
    }

    public function test_columna_contradictoria_vuelve_el_registro_ajeno(): void
    {
        $est = $this->e->a['estudiante'];
        DB::table('estudiantes')->where('id_estudiante', $est->id_estudiante)->update(['id_institucion' => $this->inst('b')]);

        $this->actingAs($this->a())->get(route('registro.estudiantes.show', $est->id_estudiante))->assertNotFound();
        $this->actingAs($this->b())->get(route('registro.estudiantes.show', $est->id_estudiante))->assertNotFound();
        $this->actingAs($this->a());
        $this->assertFalse(Estudiante::deInstitucion()->whereKey($est->id_estudiante)->exists());
    }
}
