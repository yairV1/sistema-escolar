<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Instituciones\Support\EsquemaInstitucional;
use Database\Factories\UsuarioFactory;
use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Support\EscenarioDosColegios;
use Tests\TestCase;

/**
 * Etapa 2 · B7 — sitio público en el ESQUEMA ANTIGUO (la base real hoy):
 * se revierten R2a y M1–M4 dentro de la transacción. Solo SQLite.
 */
class Etapa2B7EsquemaLegadoTest extends TestCase
{
    use RefreshDatabase;

    private const COMPARTIDO = ['Educamos con pasión y propósito', 'Nuestros estudiantes ganan el campeonato'];

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Revierte DDL dentro de la transacción de la prueba: solo SQLite.');
        }
        $this->assertSame(0, Artisan::call('migrate:rollback', ['--step' => 5, '--force' => true]));
        EsquemaInstitucional::actual()->olvidar();
        $this->assertFalse(Schema::hasColumn('landing_contenido', 'id_institucion'));
        Storage::fake('public');
    }

    public function test_varios_colegios_el_contenido_compartido_no_se_atribuye_ni_se_edita(): void
    {
        $e = EscenarioDosColegios::crear();
        $antes = DB::table('landing_contenido')->orderBy('id_contenido')->get();

        foreach (['colegio-alfa' => 'Colegio Alfa', 'colegio-zeta' => 'Colegio Zeta'] as $slug => $nombre) {
            $r = $this->get("/sitio/{$slug}")->assertOk()->assertSee('Bienvenidos a '.$nombre);
            foreach (self::COMPARTIDO as $texto) {
                $r->assertDontSee($texto);
            }
        }
        $this->actingAs($e->a['directivo'])->get(route('editar-landing.index'))->assertOk()->assertSee('data-edicion-bloqueada', false);
        $this->actingAs($e->a['directivo'])->postJson(route('editar-landing.contenido.update'), ['valores' => ['hero_titulo' => 'Mío']])->assertStatus(409);
        $this->actingAs($e->b['directivo'])->post(route('editar-landing.noticias.store'), ['titulo' => 'N', 'descripcion' => 'd', 'etiqueta' => 'e', 'fecha' => '2026-11-10', 'orden' => 1], ['Accept' => 'application/json'])->assertStatus(409);
        $idNoticia = DB::table('landing_noticias')->value('id_noticia');
        $this->actingAs($e->b['directivo'])->postJson(route('editar-landing.noticias.desactivar', $idNoticia))->assertStatus(409);
        $this->assertEquals($antes, DB::table('landing_contenido')->orderBy('id_contenido')->get(), 'el contenido compartido no cambia');

        // Admisiones: la institución sale de la ruta también en el esquema antiguo
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->flushSession();
        $this->postJson('/sitio/colegio-zeta/admision', ['nombre_acudiente' => 'M', 'apellido_acudiente' => 'P', 'correo' => 'legado@example.test', 'telefono' => '1',
            'nombre_estudiante' => 'N', 'grado_interes' => 'Grado 9°', 'id_institucion' => $e->a['institucion']->id_institucion])->assertOk();
        $this->assertSame($e->b['institucion']->id_institucion, (int) DB::table('solicitudes_admision')->where('correo', 'legado@example.test')->value('id_institucion'));
    }

    public function test_una_sola_institucion_el_sitio_y_el_editor_funcionan_como_antes(): void
    {
        (new RolesSeeder)->run();
        (new PermissionsSeeder)->run();
        (new PermissionRoleSeeder)->run();
        $this->assertSame(1, DB::table('instituciones')->count());
        $slug = DB::table('instituciones')->value('slug');
        $this->get("/sitio/{$slug}")->assertOk()->assertSee(self::COMPARTIDO[0])->assertSee(self::COMPARTIDO[1]);

        $rector = UsuarioFactory::new()->create(['id_rol' => 2, 'id_institucion' => DB::table('instituciones')->value('id_institucion')]);
        $this->actingAs($rector)->postJson(route('editar-landing.contenido.update'), ['valores' => ['hero_titulo' => 'Título editado']])->assertOk();
        $this->assertSame('Título editado', DB::table('landing_contenido')->where('clave', 'hero_titulo')->value('valor'));
        $this->assertSame(24, DB::table('landing_contenido')->count(), 'no se crean filas nuevas');
    }
}
