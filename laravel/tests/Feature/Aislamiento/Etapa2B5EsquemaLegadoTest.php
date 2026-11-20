<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Calendario\Models\EventoCategoria;
use App\Modules\GestionAcademica\Models\Materia;
use App\Modules\Instituciones\Support\EsquemaInstitucional;
use Database\Factories\EventoFactory;
use Database\Factories\UsuarioFactory;
use Database\Seeders\EventoCategoriaSeeder;
use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\EscenarioDosColegios;
use Tests\TestCase;

/**
 * Etapa 2 · B5 — compatibilidad con el ESQUEMA ANTIGUO (la base real hoy):
 * cada prueba revierte, dentro de su transacción, R2a y M1–M4, y ejecuta la
 * aplicación contra tablas SIN id_institucion. Cualquier consulta que nombrara
 * la columna fallaría con error SQL. Solo SQLite (DDL transaccional).
 */
class Etapa2B5EsquemaLegadoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Revierte DDL dentro de la transacción de la prueba: solo SQLite (MariaDB se ensaya aparte).');
        }
        $this->assertSame(0, Artisan::call('migrate:rollback', ['--step' => 5, '--force' => true]));
        EsquemaInstitucional::actual()->olvidar();
        foreach (['materias', 'periodos_academicos', 'tipos_actividad', 'evento_categorias', 'estudiantes', 'eventos', 'soportes'] as $t) {
            $this->assertFalse(Schema::hasColumn($t, 'id_institucion'), "{$t} no debería tener la columna en esquema legado");
        }
        $this->assertFalse(Schema::hasTable('migracion_pertenencia'));
    }

    private function dosColegios(): EscenarioDosColegios
    {
        return EscenarioDosColegios::crear();
    }

    public function test_pantallas_y_lecturas_funcionan_sin_las_columnas(): void
    {
        $e = $this->dosColegios();
        foreach ([$e->a['directivo'], $e->b['directivo']] as $actor) {
            foreach (['gestion-academica.materias.index', 'calificaciones.index', 'calendario.categorias.index', 'calendario.index', 'boletines.index'] as $ruta) {
                $this->actingAs($actor)->get(route($ruta))->assertOk();
            }
            // Igual que antes de B5: los catálogos son compartidos
            $this->actingAs($actor)->get(route('gestion-academica.materias.index'))->assertSee('Matemáticas');
        }
        $this->actingAs($e->a['directivo']);
        $this->assertSame(Materia::count(), Materia::deInstitucion()->count(), 'legado: todo el catálogo es visible');
    }

    public function test_con_varios_colegios_crear_o_modificar_un_catalogo_compartido_se_bloquea(): void
    {
        $e = $this->dosColegios();
        $a = $e->a['directivo'];
        $antes = Materia::count();

        $this->actingAs($a)->postJson(route('gestion-academica.materias.store'), ['nombre_materia' => 'Nueva', 'intensidad_horaria' => 2])->assertStatus(409)->assertJsonPath('success', false);
        $this->actingAs($a)->postJson(route('calificaciones.tipos-actividad.store'), ['nombre_tipo' => 'Nuevo'])->assertStatus(409);
        $this->actingAs($a)->postJson(route('calificaciones.periodos.store'), ['nombre_periodo' => 'Nuevo', 'fecha_inicio' => '2026-01-10', 'fecha_fin' => '2026-03-30', 'anio_lectivo' => 2026])->assertStatus(409);
        $this->actingAs($a)->postJson(route('calendario.categorias.store'), ['nombre' => 'Nueva', 'color' => 'var(--cat-1)', 'icono' => 'bi-star'])->assertStatus(409);
        $this->assertSame($antes, Materia::count());

        $m = $e->a['materia'];
        $this->actingAs($a)->postJson(route('gestion-academica.materias.update', $m->id_materia), ['nombre_materia' => 'Cambiada', 'intensidad_horaria' => 1])->assertStatus(409);
        $this->actingAs($e->b['directivo'])->postJson(route('gestion-academica.materias.desactivar', $m->id_materia))->assertStatus(409);
        $this->assertSame('Matemáticas', $m->fresh()->nombre_materia);
        $this->assertSame('activo', $m->fresh()->estado);

        $sistema = EventoCategoria::where('es_sistema', true)->firstOrFail();
        $this->actingAs($a)->postJson(route('calendario.categorias.update', $sistema->id_categoria), ['nombre' => 'X', 'color' => 'var(--cat-1)', 'icono' => 'bi-star'])->assertForbidden();
    }

    public function test_controles_de_la_etapa_1_se_mantienen(): void
    {
        $e = $this->dosColegios();
        $this->actingAs($e->b['directivo'])->get(route('registro.estudiantes.show', $e->a['estudiante']->id_estudiante))->assertNotFound();
        $this->actingAs($e->a['directivo'])->postJson(route('gestion-academica.asignaciones.store'), [
            'id_profesor' => $e->a['profesor']->id_profesor, 'id_materia' => $e->a['materia']->id_materia, 'id_curso' => $e->b['curso']->id_curso, 'anio_lectivo' => 2026,
        ])->assertStatus(422)->assertJsonValidationErrors('id_curso');
        // Un catálogo compartido sí es utilizable (como antes): 422 solo por el curso ajeno
    }

    public function test_perfiles_y_eventos_se_crean_sin_la_columna(): void
    {
        $e = $this->dosColegios();
        $this->actingAs($e->a['directivo']);
        $ev = EventoFactory::new()->create(['id_categoria' => $e->a['evento']->id_categoria, 'id_usuario_creador' => $e->a['directivo']->id_usuario, 'fecha_inicio' => '2026-11-26', 'fecha_fin' => '2026-11-26']);
        $this->assertArrayNotHasKey('id_institucion', $ev->fresh()->getAttributes());
    }

    public function test_con_una_sola_institucion_los_catalogos_funcionan_como_antes(): void
    {
        (new RolesSeeder)->run();
        (new PermissionsSeeder)->run();
        (new PermissionRoleSeeder)->run();
        (new EventoCategoriaSeeder)->run();
        $this->assertSame(1, DB::table('instituciones')->count(), 'la migración base crea una sola institución');
        $rector = UsuarioFactory::new()->create(['id_rol' => 2, 'id_institucion' => DB::table('instituciones')->value('id_institucion')]);

        $this->actingAs($rector)->postJson(route('gestion-academica.materias.store'), ['nombre_materia' => 'Física', 'intensidad_horaria' => 4])->assertOk();
        $id = Materia::where('nombre_materia', 'Física')->value('id_materia');
        $this->actingAs($rector)->postJson(route('gestion-academica.materias.update', $id), ['nombre_materia' => 'Física I', 'intensidad_horaria' => 4])->assertOk();
        // Unicidad global (el índice sigue siendo global en este esquema): 422, no 500
        $this->actingAs($rector)->postJson(route('gestion-academica.materias.store'), ['nombre_materia' => 'Física I', 'intensidad_horaria' => 1])->assertStatus(422)->assertJsonValidationErrors('nombre_materia');
    }
}
