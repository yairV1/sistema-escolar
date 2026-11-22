<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Auth\Mail\RecuperarPasswordMail;
use App\Modules\Calendario\DTO\OcurrenciaExpandida;
use App\Modules\Calendario\Mail\RecordatorioEventoMail;
use App\Modules\Calendario\Services\IcsExportService;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\Colegio\Models\ColegioImagen;
use App\Modules\Comunicados\Mail\ComunicadoMail;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Etapa 2 · B6 — configuración independiente por colegio (esquema expandido),
 * dos colegios ficticios. La fila de configuración sembrada por la migración
 * queda sin dueño y NO se asigna a nadie.
 */
class Etapa2B6ConfiguracionTest extends AislamientoTestCase
{
    private const DATOS_ALFA = ['nombre_colegio' => 'Colegio Alfa Oficial', 'direccion' => 'Calle Alfa 123', 'ciudad' => 'Alfaville', 'departamento' => 'Dep A', 'pais' => 'Colombia', 'telefono' => '6011111111', 'email_institucional' => 'contacto@alfa.test'];

    private const DATOS_ZETA = ['nombre_colegio' => 'Colegio Zeta Oficial', 'direccion' => 'Carrera Zeta 9', 'ciudad' => 'Zetapolis', 'departamento' => 'Dep Z', 'pais' => 'Colombia', 'telefono' => '6019999999', 'email_institucional' => 'contacto@zeta.test'];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        // Fila compartida previa (la siembra la migración solo con el nombre): se le dan
        // datos de contacto reconocibles para que las pruebas de filtración tengan sentido.
        if (! DB::table('colegio_configuracion')->exists()) {
            DB::table('colegio_configuracion')->insert(['nombre_colegio' => 'Configuración Compartida Previa', 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('colegio_configuracion')->orderBy('id_configuracion')->limit(1)->update(['direccion' => 'Dirección compartida', 'telefono' => '6015555555', 'email_institucional' => 'compartida@example.test']);
    }

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

    private function guardar($actor, array $datos)
    {
        return $this->actingAs($actor)->post(route('configuracion-colegio.update'), $datos, ['Accept' => 'application/json']);
    }

    private function compartida(): object
    {
        return DB::table('colegio_configuracion')->whereNull('id_institucion')->orderBy('id_configuracion')->first();
    }

    public function test_cada_colegio_tiene_y_edita_su_propia_configuracion(): void
    {
        $antes = $this->compartida();
        $this->guardar($this->a(), self::DATOS_ALFA)->assertOk();
        $this->guardar($this->b(), self::DATOS_ZETA)->assertOk();

        $this->assertSame('Calle Alfa 123', ColegioConfiguracion::deInstitucion($this->inst('a'))->direccion);
        $this->assertSame('Carrera Zeta 9', ColegioConfiguracion::deInstitucion($this->inst('b'))->direccion);
        $this->assertSame(1, DB::table('colegio_configuracion')->where('id_institucion', $this->inst('a'))->count());
        $this->assertSame(1, DB::table('colegio_configuracion')->where('id_institucion', $this->inst('b'))->count());
        // La configuración previa sigue intacta y sin dueño (no se asignó a nadie)
        $this->assertEquals($antes, $this->compartida());

        // Editar de nuevo actualiza la misma fila, no crea otra
        $this->guardar($this->a(), ['direccion' => 'Calle Alfa 456'] + self::DATOS_ALFA)->assertOk();
        $this->assertSame(1, DB::table('colegio_configuracion')->where('id_institucion', $this->inst('a'))->count());
        $this->assertSame('Carrera Zeta 9', ColegioConfiguracion::deInstitucion($this->inst('b'))->direccion, 'Zeta no cambia');
    }

    public function test_lectura_por_institucion_sin_filtraciones_en_pantallas(): void
    {
        $this->guardar($this->a(), self::DATOS_ALFA)->assertOk();

        $this->actingAs($this->a())->get(route('configuracion-colegio.index'))->assertOk()->assertSee('Calle Alfa 123');
        $this->actingAs($this->b())->get(route('configuracion-colegio.index'))->assertOk()
            ->assertDontSee('Calle Alfa 123')->assertDontSee('contacto@alfa.test')->assertDontSee('Dirección compartida');
        $this->actingAs($this->b())->get(route('calendario.index'))->assertOk()->assertDontSee('Colegio Alfa Oficial')->assertDontSee('Alfaville');
        // Sin configuración propia, Zeta ve una DERIVADA de su institución, nunca la compartida
        $this->assertSame('Colegio Zeta', ColegioConfiguracion::deInstitucion($this->inst('b'))->nombre_colegio);
        $this->assertFalse(ColegioConfiguracion::deInstitucion($this->inst('b'))->exists);
    }

    public function test_manipular_id_institucion_no_cambia_de_colegio(): void
    {
        $this->guardar($this->a(), self::DATOS_ALFA + ['id_institucion' => $this->inst('b'), 'id_configuracion' => 999])->assertOk();
        $this->assertSame(1, DB::table('colegio_configuracion')->where('id_institucion', $this->inst('a'))->count());
        $this->assertSame(0, DB::table('colegio_configuracion')->where('id_institucion', $this->inst('b'))->count());
        $this->assertNotContains('id_institucion', (new ColegioConfiguracion)->getFillable());
    }

    public function test_imagenes_de_otro_colegio_no_se_borran_ni_reordenan(): void
    {
        $this->actingAs($this->a())->post(route('configuracion-colegio.imagenes.store'), ['imagen' => UploadedFile::fake()->image('a.jpg'), 'tipo' => 'fachada'], ['Accept' => 'application/json'])->assertOk();
        $imgA = ColegioImagen::query()->latest('id_imagen')->first();
        $this->assertSame($this->inst('a'), (int) $imgA->id_institucion);
        $this->assertStringStartsWith('colegio/'.$this->inst('a').'/', $imgA->imagen, 'archivos separados por colegio');

        $this->actingAs($this->b())->postJson(route('configuracion-colegio.imagenes.eliminar', $imgA->id_imagen))->assertNotFound();
        $this->actingAs($this->b())->postJson(route('configuracion-colegio.imagenes.reordenar'), ['orden' => [$imgA->id_imagen]])->assertStatus(422);
        $this->assertNotNull($imgA->fresh());

        // Las imágenes de la configuración compartida tampoco son de nadie
        $cfg = $this->compartida();
        $idCompartida = DB::table('colegio_imagenes')->insertGetId(['id_configuracion' => $cfg->id_configuracion, 'imagen' => 'x.jpg', 'tipo' => 'patio', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()], 'id_imagen');
        $this->actingAs($this->a())->postJson(route('configuracion-colegio.imagenes.eliminar', $idCompartida))->assertNotFound();

        $this->actingAs($this->a())->postJson(route('configuracion-colegio.imagenes.eliminar', $imgA->id_imagen))->assertOk();
        $this->assertNull($imgA->fresh());
    }

    public function test_correos_y_exportacion_ics_usan_el_colegio_correcto(): void
    {
        $this->guardar($this->a(), self::DATOS_ALFA)->assertOk();
        $usuarioB = $this->e->b['estudiante_usuario'];

        $recuperar = new RecuperarPasswordMail($usuarioB, 'https://example.test/x');
        $this->assertStringContainsString('Colegio Zeta', $recuperar->envelope()->subject);
        $this->assertStringNotContainsString('Alfa', $recuperar->envelope()->subject.$recuperar->render());

        $comunicado = new ComunicadoMail('Aviso', 'Mensaje', 'general', $this->inst('b'));
        $html = $comunicado->render();
        $this->assertStringContainsString('Colegio Zeta', $comunicado->envelope()->subject);
        foreach (['Colegio Alfa Oficial', 'Calle Alfa 123', '6011111111', 'Dirección compartida'] as $ajeno) {
            $this->assertStringNotContainsString($ajeno, $html);
        }
        $comunicadoA = new ComunicadoMail('Aviso', 'Mensaje', 'general', $this->inst('a'));
        $this->assertStringContainsString('Calle Alfa 123', $comunicadoA->render());
        // Sin institución: marca neutra, nunca la configuración de un colegio
        $this->assertStringNotContainsString('Alfa', (new ComunicadoMail('Aviso', 'Mensaje', 'general', null))->render());

        $evB = $this->e->b['evento'];
        $dia = CarbonImmutable::parse('2026-11-20');
        $mailEvento = new RecordatorioEventoMail($evB, new OcurrenciaExpandida($dia, $dia, $dia, '08:00', '09:00', false), 30);
        $this->assertStringContainsString('Colegio Zeta', $mailEvento->envelope()->subject);
        $this->assertStringNotContainsString('Alfa Oficial', $mailEvento->render());

        $ics = app(IcsExportService::class)->generar($usuarioB, CarbonImmutable::parse('2026-11-01'), CarbonImmutable::parse('2026-11-30'));
        $this->assertStringContainsString('Colegio Zeta', $ics);
        $this->assertStringNotContainsString('Alfa Oficial', $ics);
    }

    public function test_superadmin_sin_configuracion_de_colegio(): void
    {
        $this->actingAs($this->e->superadmin)->get(route('configuracion-colegio.index'))->assertForbidden();
        $this->actingAs($this->e->superadmin);
        $neutra = ColegioConfiguracion::actual();
        $this->assertFalse($neutra->exists);
        $this->assertSame(config('app.name'), $neutra->nombre_colegio);
    }
}
