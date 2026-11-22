<?php

namespace Tests\Feature\Seguridad;

use App\Modules\Auth\Mail\RecuperarPasswordMail;
use App\Modules\Auth\Models\Usuario;
use Database\Factories\InstitucionFactory;
use Database\Factories\UsuarioFactory;
use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Corrección del incidente 2026-11-19 (docs/seguridad/incidente-superadmin-20261119.md):
 * cambiar la contraseña desde el perfil o por recuperación queda auditado (sin
 * secretos), cierra las demás sesiones y anula las cookies «recordarme».
 * Solo usuarios ficticios; SQLite en memoria y colegio_tests.
 */
class CambioDeContrasenaTest extends TestCase
{
    use RefreshDatabase;

    private const VIEJA = 'ClaveVieja-123';

    private const NUEVA = 'ClaveNueva-456';

    private Usuario $u;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesSeeder)->run();
        (new PermissionsSeeder)->run();
        (new PermissionRoleSeeder)->run();
        $inst = InstitucionFactory::new()->create(['nombre' => 'Colegio Ficticio', 'slug' => 'colegio-ficticio']);
        $this->u = UsuarioFactory::new()->create([
            'correo' => 'rector.ficticio@example.test', 'password' => Hash::make(self::VIEJA),
            'id_rol' => 2, 'id_institucion' => $inst->id_institucion, 'estado_usuario' => 'activo',
            'remember_token' => 'token-recordarme-anterior-'.str_repeat('x', 30),
        ]);
    }

    private function iniciarSesion(string $clave)
    {
        return $this->postJson('/login', ['usuario' => $this->u->correo, 'password' => $clave]);
    }

    private function auditoria()
    {
        return DB::table('audit_logs')->where('accion', 'usuario.cambiar_password');
    }

    /** Ninguna columna de la auditoría contiene la contraseña ni un hash. */
    private function assertAuditoriaSinSecretos(): void
    {
        foreach ($this->auditoria()->get() as $fila) {
            $texto = json_encode($fila);
            $this->assertStringNotContainsString(self::NUEVA, $texto);
            $this->assertStringNotContainsString(self::VIEJA, $texto);
            $this->assertDoesNotMatchRegularExpression('/\$2[aby]\$\d\d\$/', $texto);
            $this->assertStringNotContainsString((string) Usuario::find($this->u->id_usuario)->password, $texto);
        }
    }

    private function cambiarDesdePerfil(string $actual, string $nueva)
    {
        return $this->postJson(route('perfil.password'), ['password_actual' => $actual, 'password_nueva' => $nueva, 'password_nueva_confirmation' => $nueva]);
    }

    // ---------------------------------------------------------------- perfil
    public function test_perfil_cambia_audita_y_mantiene_solo_la_sesion_actual(): void
    {
        $this->iniciarSesion(self::VIEJA)->assertOk()->assertJsonPath('success', true);
        $this->get(route('perfil.show'))->assertOk();

        $this->cambiarDesdePerfil(self::VIEJA, self::NUEVA)->assertOk()->assertJsonPath('success', true);

        $u = $this->u->fresh();
        $this->assertNotNull($u->sesion_valida_desde);
        $this->assertNotSame('token-recordarme-anterior-'.str_repeat('x', 30), $u->remember_token, 'las cookies «recordarme» anteriores quedan anuladas');
        $this->assertSame(1, $this->auditoria()->count());
        $fila = $this->auditoria()->first();
        $this->assertSame($u->id_usuario, (int) $fila->entidad_id);
        $this->assertSame($u->id_usuario, (int) $fila->id_usuario, 'actor = el propio usuario');
        $this->assertSame('perfil', json_decode($fila->datos_despues, true)['origen']);
        $this->assertAuditoriaSinSecretos();

        // La sesión desde la que se cambió sigue siendo válida
        $this->get(route('perfil.show'))->assertOk();
    }

    public function test_perfil_cierra_las_demas_sesiones(): void
    {
        $this->iniciarSesion(self::VIEJA)->assertOk();
        $this->cambiarDesdePerfil(self::VIEJA, self::NUEVA)->assertOk();
        $cambio = $this->u->fresh()->sesion_valida_desde->timestamp;

        // Otra sesión abierta antes del cambio (otro navegador o dispositivo)
        Auth::forgetGuards();
        $this->flushSession();
        $this->actingAs($this->u->fresh())->withSession(['auth_at' => $cambio - 120])
            ->get(route('perfil.show'))->assertRedirect(route('login'));
        $this->assertGuest();

        Auth::forgetGuards();
        $this->flushSession();
        $this->actingAs($this->u->fresh())->withSession(['auth_at' => $cambio - 120])
            ->getJson(route('perfil.show'))->assertUnauthorized();

        // Sesión sin marca de autenticación: antes se dejaba pasar; ahora falla cerrado
        Auth::forgetGuards();
        $this->flushSession();
        $this->actingAs($this->u->fresh())->get(route('perfil.show'))->assertRedirect(route('login'));
    }

    public function test_perfil_contrasena_vieja_deja_de_funcionar_y_la_nueva_si(): void
    {
        $this->iniciarSesion(self::VIEJA)->assertOk();
        $this->cambiarDesdePerfil(self::VIEJA, self::NUEVA)->assertOk();
        $this->post('/logout');
        Auth::forgetGuards();
        $this->flushSession();

        $this->iniciarSesion(self::VIEJA)->assertJsonPath('success', false);
        $this->assertGuest();
        $this->iniciarSesion(self::NUEVA)->assertOk()->assertJsonPath('success', true);
        $this->get(route('perfil.show'))->assertOk();
    }

    public function test_perfil_con_contrasena_actual_incorrecta_no_cambia_nada(): void
    {
        $this->iniciarSesion(self::VIEJA)->assertOk();
        $antes = $this->u->fresh();
        $this->cambiarDesdePerfil('no-es-la-actual', self::NUEVA)->assertStatus(422);
        $despues = $this->u->fresh();
        $this->assertSame($antes->password, $despues->password);
        $this->assertSame($antes->remember_token, $despues->remember_token);
        $this->assertEquals($antes->sesion_valida_desde, $despues->sesion_valida_desde);
        $this->assertSame(0, $this->auditoria()->count());
    }

    // ----------------------------------------------------------- recuperación
    private function enlaceDeRecuperacion(): string
    {
        Mail::fake();
        $this->postJson('/forgot-password', ['correo' => $this->u->correo])->assertOk();
        $token = null;
        Mail::assertSent(RecuperarPasswordMail::class, function (RecuperarPasswordMail $m) use (&$token) {
            parse_str((string) parse_url($m->enlace, PHP_URL_QUERY), $q);
            $token = $q['token'] ?? null;

            return true;
        });
        $this->assertNotEmpty($token);

        return $token;
    }

    public function test_recuperacion_cambia_audita_e_invalida_todas_las_sesiones(): void
    {
        $sesionPrevia = now()->subMinutes(5)->timestamp;
        $token = $this->enlaceDeRecuperacion();

        $this->postJson('/reset-password', ['token' => $token, 'password' => self::NUEVA, 'password_confirmation' => self::NUEVA])
            ->assertOk()->assertJsonPath('success', true);

        $u = $this->u->fresh();
        $this->assertNotNull($u->sesion_valida_desde);
        $this->assertNotSame('token-recordarme-anterior-'.str_repeat('x', 30), $u->remember_token);
        $fila = $this->auditoria()->first();
        $this->assertNotNull($fila);
        $this->assertSame($u->id_usuario, (int) $fila->entidad_id);
        $this->assertNull($fila->id_usuario, 'sin sesión: el actor no se inventa');
        $this->assertSame('recuperacion', json_decode($fila->datos_despues, true)['origen']);
        $this->assertAuditoriaSinSecretos();

        // Una sesión abierta antes de la recuperación queda cerrada
        $this->actingAs($u)->withSession(['auth_at' => $sesionPrevia])->get(route('perfil.show'))->assertRedirect(route('login'));

        // El enlace no se reutiliza
        Auth::forgetGuards();
        $this->flushSession();
        $this->postJson('/reset-password', ['token' => $token, 'password' => 'Otra-789', 'password_confirmation' => 'Otra-789'])->assertStatus(400);
        $this->assertSame(1, $this->auditoria()->count());
    }

    public function test_recuperacion_contrasena_vieja_deja_de_funcionar_y_la_nueva_si(): void
    {
        $token = $this->enlaceDeRecuperacion();
        $this->postJson('/reset-password', ['token' => $token, 'password' => self::NUEVA, 'password_confirmation' => self::NUEVA])->assertOk();

        $this->iniciarSesion(self::VIEJA)->assertJsonPath('success', false);
        $this->assertGuest();
        $this->iniciarSesion(self::NUEVA)->assertOk()->assertJsonPath('success', true);
        $this->get(route('perfil.show'))->assertOk();
    }

    // ------------------------------------------------------------ recordarme
    public function test_la_cookie_recordarme_anterior_no_vuelve_a_entrar(): void
    {
        $guard = Auth::guard('web');
        $cookie = $guard->getRecallerName();
        $tokenViejo = $this->u->remember_token;
        $hashViejo = $this->u->password;

        // Antes del cambio, la cookie es válida
        $this->withCookie($cookie, $this->u->id_usuario.'|'.$tokenViejo.'|'.$hashViejo)->get(route('perfil.show'))->assertOk();

        // Otro navegador: sin esa cookie, inicia sesión y cambia la contraseña
        $this->defaultCookies = [];
        Auth::forgetGuards();
        $this->flushSession();
        $this->iniciarSesion(self::VIEJA)->assertOk();
        $this->cambiarDesdePerfil(self::VIEJA, self::NUEVA)->assertOk();

        // Otro dispositivo con la cookie anterior: no entra
        Auth::forgetGuards();
        $this->flushSession();
        $this->withCookie($cookie, $this->u->id_usuario.'|'.$tokenViejo.'|'.$hashViejo)->get(route('perfil.show'))->assertRedirect(route('login'));
        $this->assertGuest();

        // Una cookie VIGENTE (emitida tras el cambio) sí entra: el middleware la marca y no la expulsa
        $u = $this->u->fresh();
        $this->defaultCookies = [];
        Auth::forgetGuards();
        $this->flushSession();
        $this->withCookie($cookie, $u->id_usuario.'|'.$u->remember_token.'|'.$u->password)->get(route('perfil.show'))->assertOk();
        $this->assertAuthenticatedAs($u);
    }
}
