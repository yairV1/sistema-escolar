<?php

namespace Tests\Feature\Seguridad\Fase1;

use Illuminate\Support\Facades\Route;
use RuntimeException;

/**
 * P0-2: con APP_DEBUG=false las páginas de error no revelan código, rutas ni
 * mensajes internos, y la compuerta seguridad:verificar-exposicion bloquea
 * una configuración no apta para exponer el sistema.
 */
class ExposicionSeguraTest extends Fase1TestCase
{
    private const SECRETO = 'DETALLE-INTERNO-NO-DEBE-VERSE';

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware('web')->get('/_fase1/error', fn () => throw new RuntimeException(self::SECRETO));
    }

    public function test_error_500_sin_debug_no_revela_detalles(): void
    {
        config(['app.debug' => false]);

        foreach ([$this->get('/_fase1/error'), $this->getJson('/_fase1/error')] as $r) {
            $r->assertStatus(500);
            $contenido = $r->getContent();
            $this->assertStringNotContainsString(self::SECRETO, $contenido);
            $this->assertStringNotContainsString('RuntimeException', $contenido);
            $this->assertStringNotContainsString(base_path(), $contenido);
            $this->assertStringNotContainsString('vendor/laravel', $contenido);
        }
    }

    public function test_con_debug_activo_si_se_ve_el_detalle_por_eso_debe_ir_en_false(): void
    {
        config(['app.debug' => true]);
        $this->getJson('/_fase1/error')->assertStatus(500)->assertSee(self::SECRETO);
    }

    public function test_404_sin_debug_es_generico(): void
    {
        config(['app.debug' => false]);
        $r = $this->get('/ruta-que-no-existe-fase1');
        $r->assertNotFound();
        $this->assertStringNotContainsString(base_path(), $r->getContent());
    }

    public function test_la_compuerta_bloquea_la_configuracion_de_desarrollo(): void
    {
        config(['app.debug' => true, 'app.url' => 'http://localhost', 'session.secure' => false]);

        $this->artisan('seguridad:verificar-exposicion')->assertExitCode(1);
    }

    public function test_la_compuerta_rechaza_confiar_en_todos_los_proxies(): void
    {
        $this->configuracionExpuestaValida();
        config(['seguridad.proxies_confiables' => ['*']]);

        $this->artisan('seguridad:verificar-exposicion')->assertExitCode(1);
    }

    public function test_la_compuerta_pasa_con_configuracion_expuesta_valida(): void
    {
        $this->configuracionExpuestaValida();

        $this->artisan('seguridad:verificar-exposicion')->assertExitCode(0);
        $this->artisan('seguridad:verificar-exposicion --ngrok')->assertExitCode(0);
    }

    public function test_la_compuerta_ngrok_exige_el_proxy_local(): void
    {
        $this->configuracionExpuestaValida();
        config(['seguridad.proxies_confiables' => []]);

        $this->artisan('seguridad:verificar-exposicion --ngrok')->assertExitCode(1);
    }

    private function configuracionExpuestaValida(): void
    {
        $this->app['env'] = 'production';
        config([
            'app.debug' => false,
            'app.url' => 'https://colegio.example.test',
            'seguridad.validar_hosts' => null,
            'seguridad.proxies_confiables' => ['127.0.0.1', '::1'],
            'session.secure' => true,
        ]);
    }
}
