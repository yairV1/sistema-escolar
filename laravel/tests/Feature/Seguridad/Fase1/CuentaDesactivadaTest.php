<?php

namespace Tests\Feature\Seguridad\Fase1;

use App\Modules\Auth\Mail\RecuperarPasswordMail;
use App\Modules\Auth\Models\PasswordReset;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Instituciones\Models\Institucion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * P1-1: una cuenta desactivada pierde el acceso en su siguiente petición
 * (sesión o cookie «recordarme»), en el panel y fuera de él, y no lo
 * recupera con una sesión o cookie anterior aunque se reactive.
 */
class CuentaDesactivadaTest extends Fase1TestCase
{
    private Institucion $inst;

    private Usuario $rector;

    private Usuario $coordinador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inst = $this->institucion('colegio-cuentas');
        $this->rector = $this->usuario(2, $this->inst);
        $this->coordinador = $this->usuario(3, $this->inst, ['remember_token' => str_repeat('r', 60)]);
    }

    /** Cambia el estado en BD y recarga al usuario autenticado, como pasa en cada petición real. */
    private function desactivar(Usuario $u, string $estado = 'inactivo'): void
    {
        DB::table('usuarios')->where('id_usuario', $u->id_usuario)->update(['estado_usuario' => $estado]);
        Auth::guard('web')->user()?->refresh();
    }

    public function test_sesion_abierta_se_corta_en_rutas_del_panel_y_fuera_de_el(): void
    {
        // /inicio usa FIELD() (solo MariaDB); se prueba otra ruta del panel.
        foreach (['/listados/estudiantes', '/perfil', '/calendario', '/soporte/mis-solicitudes'] as $ruta) {
            $this->nuevoNavegador();
            $this->iniciarSesion($this->rector)->assertOk();
            $this->get($ruta)->assertOk();

            $this->desactivar($this->rector);
            $this->get($ruta)->assertRedirect(route('login'))->assertSessionHas('status');
            $this->assertGuest();

            DB::table('usuarios')->where('id_usuario', $this->rector->id_usuario)->update(['estado_usuario' => 'activo']);
        }
    }

    public function test_peticion_json_de_cuenta_desactivada_recibe_401(): void
    {
        $this->actingAs($this->rector);
        $this->desactivar($this->rector);

        $this->getJson('/notificaciones')->assertStatus(401)->assertJsonPath('success', false);
        $this->assertGuest();
    }

    public function test_cuenta_bloqueada_tambien_pierde_acceso(): void
    {
        $this->actingAs($this->rector);
        $this->desactivar($this->rector, 'bloqueado');

        $this->get('/perfil')->assertRedirect(route('login'));
    }

    public function test_cookie_recordarme_de_cuenta_desactivada_no_restaura_la_sesion(): void
    {
        [$nombre, $valor] = $this->cookieRecordarme($this->coordinador);
        $this->withCookie($nombre, $valor)->get('/perfil')->assertOk();

        $this->desactivar($this->coordinador);
        $this->nuevoNavegador();
        $this->withCookie($nombre, $valor)->get('/perfil')->assertRedirect(route('login'));
        $this->assertGuest();

        // El logout rotó el token: la cookie vieja tampoco sirve tras reactivar.
        DB::table('usuarios')->where('id_usuario', $this->coordinador->id_usuario)->update(['estado_usuario' => 'activo']);
        $this->nuevoNavegador();
        $this->withCookie($nombre, $valor)->get('/perfil')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_desactivar_desde_listados_anula_sesiones_y_cookie_aunque_se_reactive(): void
    {
        [$nombre, $valor] = $this->cookieRecordarme($this->coordinador);

        // Sesión del coordinador en su navegador.
        $this->iniciarSesion($this->coordinador)->assertOk();
        $sesionCoordinador = $this->app['session']->all();
        $this->travel(5)->seconds();

        // El rector lo desactiva y lo reactiva desde Listados.
        $this->nuevoNavegador();
        $this->actingAs($this->rector);
        $url = "/listados/administrativos/{$this->coordinador->id_usuario}";
        $this->postJson("{$url}/desactivar")->assertOk();
        $c = $this->coordinador->fresh();
        $this->assertNull($c->remember_token);
        $this->assertNotNull($c->sesion_valida_desde);
        $this->postJson("{$url}/activar")->assertOk();

        // La sesión anterior no revive.
        $this->nuevoNavegador();
        $this->withSession($sesionCoordinador)->get('/perfil')->assertRedirect(route('login'));
        $this->assertGuest();

        // La cookie «recordarme» anterior tampoco.
        $this->nuevoNavegador();
        $this->withCookie($nombre, $valor)->get('/perfil')->assertRedirect(route('login'));
        $this->assertGuest();

        // Un login nuevo sí funciona.
        $this->nuevoNavegador();
        $this->iniciarSesion($this->coordinador)->assertOk();
        $this->get('/perfil')->assertOk();
    }

    public function test_login_de_cuenta_desactivada_se_rechaza(): void
    {
        $this->desactivar($this->rector);
        $this->iniciarSesion($this->rector)->assertStatus(403);
        $this->assertGuest();
    }

    public function test_recuperacion_no_se_envia_ni_se_aplica_a_cuentas_desactivadas(): void
    {
        Mail::fake();
        $token = PasswordReset::generarPara($this->rector);   // emitido antes de desactivar
        $this->desactivar($this->rector);

        $this->postJson('/forgot-password', ['correo' => $this->rector->correo])->assertOk();
        Mail::assertNotSent(RecuperarPasswordMail::class);

        $this->postJson('/reset-password', ['token' => $token, 'password' => 'Otra-Clave-456', 'password_confirmation' => 'Otra-Clave-456'])
            ->assertStatus(400);
        $this->assertTrue(Hash::check(self::CLAVE, $this->rector->fresh()->password));
    }

    public function test_suscripcion_ics_de_cuenta_desactivada_responde_404(): void
    {
        DB::table('usuarios')->where('id_usuario', $this->rector->id_usuario)->update(['ics_token' => str_repeat('a', 64)]);
        $url = route('calendario.ics', ['usuario' => $this->rector->id_usuario, 'token' => str_repeat('a', 64)]);
        $this->get($url)->assertOk();

        $this->desactivar($this->rector);
        $this->get($url)->assertNotFound();
    }

    public function test_cambio_de_rol_institucional_anula_las_sesiones_del_usuario(): void
    {
        $this->iniciarSesion($this->coordinador)->assertOk();
        $sesionCoordinador = $this->app['session']->all();
        $this->travel(5)->seconds();

        $this->nuevoNavegador();
        $this->actingAs($this->rector);
        $c = $this->coordinador;
        $this->postJson("/registro/administrativos/{$c->id_usuario}", [
            'nombres' => $c->nombres, 'apellidos' => $c->apellidos, 'tipo_documento' => $c->tipo_documento,
            'numero_documento' => $c->numero_documento, 'correo' => $c->correo, 'telefono' => null, 'id_rol' => 4,
        ])->assertOk();
        $this->assertSame(4, (int) $c->fresh()->id_rol);
        $this->assertNull($c->fresh()->remember_token);

        $this->nuevoNavegador();
        $this->withSession($sesionCoordinador)->get('/perfil')->assertRedirect(route('login'));
    }

    public function test_editar_datos_sin_cambiar_rol_no_cierra_sesiones(): void
    {
        $this->actingAs($this->rector);
        $c = $this->coordinador;
        $this->postJson("/registro/administrativos/{$c->id_usuario}", [
            'nombres' => 'Nombre Nuevo', 'apellidos' => $c->apellidos, 'tipo_documento' => $c->tipo_documento,
            'numero_documento' => $c->numero_documento, 'correo' => $c->correo, 'telefono' => null, 'id_rol' => 3,
        ])->assertOk();

        $this->assertNull($c->fresh()->sesion_valida_desde);
        $this->assertSame(str_repeat('r', 60), $c->fresh()->remember_token);
    }

    public function test_rol_inactivo_corta_el_acceso(): void
    {
        $this->actingAs($this->rector);
        DB::table('roles')->where('id_rol', 2)->update(['estado' => 'inactivo']);
        Auth::guard('web')->user()->refresh();

        $this->get('/listados/estudiantes')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
