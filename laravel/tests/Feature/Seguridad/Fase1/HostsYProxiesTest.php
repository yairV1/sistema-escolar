<?php

namespace Tests\Feature\Seguridad\Fase1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * P1-4: la cabecera Host se limita a los hosts del servidor y las cabeceras
 * X-Forwarded-* solo se aceptan de proxies configurados (nunca «todos»), de
 * modo que IP, esquema y host no se pueden falsear, ni con ellos los límites.
 */
class HostsYProxiesTest extends Fase1TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://colegio.example.test']);
        Route::middleware('web')->get('/_fase1/origen', fn (Request $r) => [
            'ip' => $r->ip(), 'esquema' => $r->getScheme(), 'host' => $r->getHost(),
        ]);
    }

    private function origen(array $cabeceras = [], string $remoto = '127.0.0.1', string $url = 'https://colegio.example.test/_fase1/origen')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $remoto])->withHeaders($cabeceras)->getJson($url);
    }

    // ------------------------------------------------------------------ Host
    public function test_host_validado_acepta_solo_los_del_servidor(): void
    {
        config(['seguridad.validar_hosts' => true, 'seguridad.hosts_confiables' => ['www.colegio.example.test']]);

        $this->get('https://colegio.example.test/login')->assertOk();
        $this->get('https://www.colegio.example.test/login')->assertOk();
        $this->get('https://atacante.example/login')->assertStatus(400);
        $this->get('https://sub.colegio.example.test/login')->assertStatus(400);   // sin subdominios implícitos
        $this->get('https://colegio.example.test.atacante.example/login')->assertStatus(400);
    }

    public function test_comodines_en_la_lista_de_hosts_se_ignoran(): void
    {
        config(['seguridad.validar_hosts' => true, 'seguridad.hosts_confiables' => ['*', '*.example']]);

        $this->get('https://atacante.example/login')->assertStatus(400);
        $this->get('https://colegio.example.test/login')->assertOk();
    }

    public function test_validacion_automatica_activa_en_produccion_e_inactiva_en_testing(): void
    {
        config(['seguridad.validar_hosts' => null]);
        $this->get('https://atacante.example/login')->assertOk();   // testing: sin validar (como local)

        $this->app['env'] = 'production';
        $this->get('https://atacante.example/login')->assertStatus(400);
    }

    /** Un APP_VALIDATE_HOSTS vacío o mal escrito (p. ej. copiado de .env.example) no desactiva la validación. */
    public function test_valor_vacio_o_invalido_de_app_validate_hosts_queda_en_automatico(): void
    {
        $leer = function (?string $valor) {
            if ($valor === null) {
                putenv('APP_VALIDATE_HOSTS');
                unset($_ENV['APP_VALIDATE_HOSTS'], $_SERVER['APP_VALIDATE_HOSTS']);
            } else {
                putenv("APP_VALIDATE_HOSTS={$valor}");
                $_ENV['APP_VALIDATE_HOSTS'] = $_SERVER['APP_VALIDATE_HOSTS'] = $valor;
            }

            return (require config_path('seguridad.php'))['validar_hosts'];
        };

        try {
            foreach ([null, '', 'null', 'ture', 'sí'] as $valor) {
                $this->assertNull($leer($valor), 'APP_VALIDATE_HOSTS='.var_export($valor, true));
            }
            $this->assertTrue($leer('true'));
            $this->assertFalse($leer('false'));
        } finally {
            $leer(null);
        }

        // Automático + producción = validación activa.
        config(['seguridad.validar_hosts' => null]);
        $this->app['env'] = 'production';
        $this->get('https://atacante.example/login')->assertStatus(400);
    }

    // --------------------------------------------------------------- proxies
    public function test_sin_proxies_configurados_las_cabeceras_reenviadas_se_ignoran(): void
    {
        config(['seguridad.proxies_confiables' => []]);

        $this->origen(['X-Forwarded-For' => '203.0.113.9', 'X-Forwarded-Proto' => 'http', 'X-Forwarded-Host' => 'atacante.example'])
            ->assertExactJson(['ip' => '127.0.0.1', 'esquema' => 'https', 'host' => 'colegio.example.test']);
    }

    public function test_proxy_configurado_aporta_ip_y_esquema_reales_pero_no_el_host(): void
    {
        config(['seguridad.proxies_confiables' => ['127.0.0.1', '::1']]);

        $this->origen(['X-Forwarded-For' => '203.0.113.9', 'X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'atacante.example'], url: 'http://colegio.example.test/_fase1/origen')
            ->assertExactJson(['ip' => '203.0.113.9', 'esquema' => 'https', 'host' => 'colegio.example.test']);
    }

    public function test_peticion_directa_desde_ip_no_confiable_no_puede_falsear_cabeceras(): void
    {
        config(['seguridad.proxies_confiables' => ['127.0.0.1']]);

        $this->origen(['X-Forwarded-For' => '203.0.113.9', 'X-Forwarded-Proto' => 'http'], remoto: '198.51.100.7')
            ->assertExactJson(['ip' => '198.51.100.7', 'esquema' => 'https', 'host' => 'colegio.example.test']);
    }

    public function test_confiar_en_todos_se_rechaza(): void
    {
        foreach (['*', '**', '0.0.0.0/0', '::/0', 'REMOTE_ADDR', '10.0.0.0/4', 'no-es-ip'] as $entrada) {
            config(['seguridad.proxies_confiables' => [$entrada]]);
            $this->origen(['X-Forwarded-For' => '203.0.113.9'], remoto: '198.51.100.7')
                ->assertJsonPath('ip', '198.51.100.7');
        }
    }

    public function test_host_on_forge_no_activa_la_confianza_total_de_laravel(): void
    {
        config(['seguridad.proxies_confiables' => []]);

        $this->origen(['X-Forwarded-For' => '203.0.113.9'], remoto: '198.51.100.7', url: 'https://x.on-forge.com/_fase1/origen')
            ->assertJsonPath('ip', '198.51.100.7');
    }

    public function test_x_forwarded_host_solo_con_opcion_explicita_y_aun_asi_validado(): void
    {
        config(['seguridad.proxies_confiables' => ['127.0.0.1'], 'seguridad.confiar_host_reenviado' => true, 'seguridad.validar_hosts' => true]);

        $this->origen(['X-Forwarded-Host' => 'colegio.example.test'], url: 'http://interno.local/_fase1/origen')
            ->assertOk()->assertJsonPath('host', 'colegio.example.test');
        $this->origen(['X-Forwarded-Host' => 'atacante.example'])->assertStatus(400);
    }

    // ---------------------------------------------------------------- límites
    public function test_falsear_x_forwarded_for_no_evade_el_limite_de_login(): void
    {
        config(['seguridad.proxies_confiables' => []]);

        for ($i = 0; $i < 20; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
                ->withHeaders(['X-Forwarded-For' => "203.0.113.{$i}"])
                ->postJson('/login', ['usuario' => "nadie{$i}@example.test", 'password' => 'incorrecta'])->assertStatus(401);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->withHeaders(['X-Forwarded-For' => '203.0.113.250'])
            ->postJson('/login', ['usuario' => 'otro@example.test', 'password' => 'incorrecta'])->assertStatus(429);
    }

    public function test_detras_del_proxy_cada_cliente_tiene_su_propio_limite(): void
    {
        config(['seguridad.proxies_confiables' => ['127.0.0.1']]);

        for ($i = 0; $i < 20; $i++) {
            $this->withHeaders(['X-Forwarded-For' => '203.0.113.1'])
                ->postJson('/login', ['usuario' => "nadie{$i}@example.test", 'password' => 'incorrecta'])->assertStatus(401);
        }
        $this->withHeaders(['X-Forwarded-For' => '203.0.113.1'])
            ->postJson('/login', ['usuario' => 'otro@example.test', 'password' => 'incorrecta'])->assertStatus(429);

        // Otro cliente detrás del mismo proxy (ngrok) no queda bloqueado.
        $this->withHeaders(['X-Forwarded-For' => '203.0.113.2'])
            ->postJson('/login', ['usuario' => 'otro@example.test', 'password' => 'incorrecta'])->assertStatus(401);
    }

    public function test_recuperacion_de_contrasena_tiene_limite(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/forgot-password', ['correo' => "persona{$i}@example.test"])->assertOk();
        }
        $this->postJson('/forgot-password', ['correo' => 'persona9@example.test'])->assertStatus(429);
    }

    public function test_el_limite_de_reset_no_es_una_clave_comun_a_todos(): void
    {
        // Desde IPs distintas, tokens distintos: ninguna queda bloqueada por las demás.
        for ($i = 0; $i < 8; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => "198.51.100.{$i}"])
                ->postJson('/reset-password', ['token' => "token-{$i}", 'password' => 'Clave-123', 'password_confirmation' => 'Clave-123'])
                ->assertStatus(400);
        }
    }
}
