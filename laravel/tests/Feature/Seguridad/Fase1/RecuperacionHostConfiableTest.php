<?php

namespace Tests\Feature\Seguridad\Fase1;

use App\Modules\Auth\Mail\RecuperarPasswordMail;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * P0-1: el enlace de recuperación usa APP_URL validada, nunca la cabecera Host
 * ni X-Forwarded-Host de quien hace la petición.
 */
class RecuperacionHostConfiableTest extends Fase1TestCase
{
    private const APP_URL = 'https://colegio.example.test';

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['app.url' => self::APP_URL]);
    }

    private function enlaceEnviado(): string
    {
        $enlace = null;
        Mail::assertSent(RecuperarPasswordMail::class, function (RecuperarPasswordMail $m) use (&$enlace) {
            $enlace = $m->enlace;

            return true;
        });

        return $enlace;
    }

    public function test_host_malicioso_no_cambia_el_dominio_del_enlace(): void
    {
        $u = $this->usuario(2, $this->institucion('colegio-host'));

        // Validación de Host desactivada (como en local): la petición llega con Host atacante.
        config(['seguridad.validar_hosts' => false]);
        $this->postJson('http://atacante.example/forgot-password', ['correo' => $u->correo])->assertOk();

        $enlace = $this->enlaceEnviado();
        $this->assertStringStartsWith(self::APP_URL.'/reset-password?token=', $enlace);
        $this->assertStringNotContainsString('atacante', $enlace);
        $this->assertSame('colegio.example.test', parse_url($enlace, PHP_URL_HOST));
    }

    public function test_x_forwarded_host_de_un_proxy_no_cambia_el_enlace(): void
    {
        $u = $this->usuario(2, $this->institucion('colegio-xfh'));
        config(['seguridad.validar_hosts' => false, 'seguridad.proxies_confiables' => ['127.0.0.1']]);

        $this->withHeaders(['X-Forwarded-Host' => 'atacante.example', 'X-Forwarded-Proto' => 'http'])
            ->postJson('/forgot-password', ['correo' => $u->correo])->assertOk();

        $this->assertStringStartsWith(self::APP_URL.'/', $this->enlaceEnviado());
    }

    public function test_con_validacion_activa_un_host_ajeno_se_rechaza_sin_enviar_nada(): void
    {
        $u = $this->usuario(2, $this->institucion('colegio-validado'));
        config(['seguridad.validar_hosts' => true]);

        $this->postJson('http://atacante.example/forgot-password', ['correo' => $u->correo])->assertStatus(400);
        Mail::assertNothingSent();
        $this->assertSame(0, DB::table('password_resets')->count());

        // El host del servidor sí funciona.
        $this->postJson(self::APP_URL.'/forgot-password', ['correo' => $u->correo])->assertOk();
        $this->assertStringStartsWith(self::APP_URL.'/reset-password?token=', $this->enlaceEnviado());
    }

    public function test_app_url_invalida_falla_cerrado_sin_token_ni_correo(): void
    {
        $u = $this->usuario(2, $this->institucion('colegio-url-mala'));

        foreach (['', 'javascript:alert(1)', 'https://user:pass@colegio.example.test', 'https://colegio.example.test/?x=1', 'ftp://colegio.example.test'] as $url) {
            config(['app.url' => $url]);
            $this->postJson('/forgot-password', ['correo' => $u->correo])->assertOk()->assertJsonPath('success', true);
        }

        Mail::assertNothingSent();
        $this->assertSame(0, DB::table('password_resets')->count());
    }

    public function test_en_produccion_no_se_emiten_enlaces_http(): void
    {
        $u = $this->usuario(2, $this->institucion('colegio-prod'));
        $this->app['env'] = 'production';
        // En producción Laravel exige CSRF; aquí se prueba solo el enlace.
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['app.url' => 'http://colegio.example.test', 'seguridad.validar_hosts' => false]);

        $this->postJson('/forgot-password', ['correo' => $u->correo])->assertOk();
        Mail::assertNothingSent();

        config(['app.url' => self::APP_URL]);
        $this->postJson('/forgot-password', ['correo' => $u->correo])->assertOk();
        $this->assertStringStartsWith('https://', $this->enlaceEnviado());
    }

    public function test_el_enlace_emitido_sigue_funcionando(): void
    {
        $u = $this->usuario(2, $this->institucion('colegio-flujo'));
        $this->postJson('/forgot-password', ['correo' => $u->correo])->assertOk();
        parse_str((string) parse_url($this->enlaceEnviado(), PHP_URL_QUERY), $q);

        $this->postJson('/reset-password', [
            'token' => $q['token'], 'password' => 'Nueva-Clave-789', 'password_confirmation' => 'Nueva-Clave-789',
        ])->assertOk()->assertJsonPath('success', true);
    }
}
