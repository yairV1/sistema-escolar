<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Auth\Mail\RecuperarPasswordMail;
use App\Modules\Calendario\Services\IcsExportService;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\Comunicados\Mail\ComunicadoMail;
use App\Modules\Instituciones\Support\EsquemaInstitucional;
use Carbon\CarbonImmutable;
use Database\Factories\UsuarioFactory;
use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Support\EscenarioDosColegios;
use Tests\TestCase;

/**
 * Etapa 2 · B6 — configuración en el ESQUEMA ANTIGUO (sin id_institucion; la
 * base real hoy). Se revierten R2a y M1–M4 dentro de la transacción. Solo SQLite.
 */
class Etapa2B6EsquemaLegadoTest extends TestCase
{
    use RefreshDatabase;

    private const DATOS = ['nombre_colegio' => 'Nombre Editado', 'direccion' => 'Calle Única 1', 'ciudad' => 'C', 'departamento' => 'D', 'pais' => 'Colombia', 'telefono' => '6010000000', 'email_institucional' => 'unica@example.test'];

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Revierte DDL dentro de la transacción de la prueba: solo SQLite.');
        }
        $this->assertSame(0, Artisan::call('migrate:rollback', ['--step' => 5, '--force' => true]));
        EsquemaInstitucional::actual()->olvidar();
        $this->assertFalse(Schema::hasColumn('colegio_configuracion', 'id_institucion'));
        Storage::fake('public');
        // Fila compartida previa (la siembra la migración solo con el nombre): se le dan
        // datos de contacto reconocibles para que las pruebas de filtración tengan sentido.
        if (! DB::table('colegio_configuracion')->exists()) {
            DB::table('colegio_configuracion')->insert(['nombre_colegio' => 'Configuración Compartida Previa', 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('colegio_configuracion')->orderBy('id_configuracion')->limit(1)->update(['direccion' => 'Dirección compartida', 'telefono' => '6015555555', 'email_institucional' => 'compartida@example.test']);
    }

    private function compartida(): object
    {
        return DB::table('colegio_configuracion')->orderBy('id_configuracion')->first();
    }

    public function test_varios_colegios_la_configuracion_compartida_no_se_edita_ni_se_filtra(): void
    {
        $e = EscenarioDosColegios::crear();
        $antes = $this->compartida();

        $this->actingAs($e->a['directivo'])->get(route('configuracion-colegio.index'))->assertOk()
            ->assertSee('data-edicion-bloqueada', false)->assertDontSee($antes->direccion);
        $this->actingAs($e->a['directivo'])->post(route('configuracion-colegio.update'), self::DATOS, ['Accept' => 'application/json'])->assertStatus(409);
        $this->actingAs($e->b['directivo'])->post(route('configuracion-colegio.imagenes.store'), ['imagen' => UploadedFile::fake()->image('b.jpg'), 'tipo' => 'patio'], ['Accept' => 'application/json'])->assertStatus(409);
        $this->assertEquals($antes, $this->compartida(), 'la fila compartida no cambia');

        // Cada colegio ve una configuración derivada de su institución, no la compartida
        $this->assertSame('Colegio Zeta', ColegioConfiguracion::deInstitucion($e->b['institucion']->id_institucion)->nombre_colegio);
        $this->assertStringContainsString('Colegio Zeta', (new RecuperarPasswordMail($e->b['estudiante_usuario'], 'https://x.test'))->envelope()->subject);
        $this->assertStringNotContainsString($antes->direccion, (new ComunicadoMail('A', 'M', 'general', $e->b['institucion']->id_institucion))->render());
        $ics = app(IcsExportService::class)->generar($e->b['estudiante_usuario'], CarbonImmutable::parse('2026-11-01'), CarbonImmutable::parse('2026-11-30'));
        $this->assertStringContainsString('Colegio Zeta', $ics);
        $this->assertStringNotContainsString($antes->nombre_colegio, $ics);

        // B7: el login general (sin sesión) usa la marca neutral de la plataforma, no la fila compartida
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->flushSession();
        $this->get('/login')->assertOk()->assertSee(config('app.name'))->assertDontSee($antes->nombre_colegio);
    }

    public function test_una_sola_institucion_funciona_como_antes(): void
    {
        (new RolesSeeder)->run();
        (new PermissionsSeeder)->run();
        (new PermissionRoleSeeder)->run();
        $this->assertSame(1, DB::table('instituciones')->count());
        $rector = UsuarioFactory::new()->create(['id_rol' => 2, 'id_institucion' => DB::table('instituciones')->value('id_institucion')]);

        $this->actingAs($rector)->get(route('configuracion-colegio.index'))->assertOk()->assertDontSee('data-edicion-bloqueada', false)->assertSee($this->compartida()->direccion);
        $this->actingAs($rector)->post(route('configuracion-colegio.update'), self::DATOS, ['Accept' => 'application/json'])->assertOk();
        $this->assertSame('Calle Única 1', $this->compartida()->direccion);
        $this->assertSame(1, DB::table('colegio_configuracion')->count(), 'sigue habiendo una sola fila');
    }
}
