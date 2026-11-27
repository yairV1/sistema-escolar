<?php

namespace Tests\Feature\Seguridad\Fase1;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Instituciones\Models\Institucion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * P1-2: las cuentas de una institución suspendida o inactiva pierden el acceso
 * (sesión y «recordarme») y no pueden iniciar sesión; al reactivarla pueden
 * volver a entrar, pero las sesiones anteriores no reviven. SuperAdmin y las
 * demás instituciones no se ven afectados; los flujos públicos siguen.
 */
class InstitucionSuspendidaTest extends Fase1TestCase
{
    private Institucion $alfa;

    private Institucion $beta;

    private Usuario $rectorAlfa;

    private Usuario $docenteAlfa;

    private Usuario $rectorBeta;

    private Usuario $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alfa = $this->institucion('colegio-alfa-f1');
        $this->beta = $this->institucion('colegio-beta-f1');
        $this->rectorAlfa = $this->usuario(2, $this->alfa);
        $this->docenteAlfa = $this->usuario(5, $this->alfa, ['remember_token' => str_repeat('d', 60)]);
        $this->rectorBeta = $this->usuario(2, $this->beta);
        $this->superadmin = $this->usuario(8, null);
    }

    private function estado(Institucion $i, string $estado): void
    {
        DB::table('instituciones')->where('id_institucion', $i->id_institucion)->update(['estado' => $estado]);
        $actual = Auth::guard('web')->user();
        $actual?->refresh();
        $actual?->unsetRelation('institucion');
    }

    public function test_suspendida_e_inactiva_bloquean_la_sesion_abierta(): void
    {
        foreach (['suspendida', 'inactiva'] as $estado) {
            $this->nuevoNavegador();
            $this->estado($this->alfa, 'activa');
            $this->iniciarSesion($this->rectorAlfa)->assertOk();
            $this->get('/perfil')->assertOk();

            $this->estado($this->alfa, $estado);
            $this->get('/perfil')->assertRedirect(route('login'))
                ->assertSessionHas('status', Usuario::mensajeDeBloqueo(Usuario::BLOQUEO_INSTITUCION));
            $this->assertGuest();
        }
    }

    public function test_json_de_institucion_suspendida_recibe_401(): void
    {
        $this->actingAs($this->rectorAlfa);
        $this->estado($this->alfa, 'suspendida');

        $this->getJson('/notificaciones')->assertStatus(401);
    }

    public function test_login_de_institucion_suspendida_se_rechaza_con_su_motivo(): void
    {
        $this->estado($this->alfa, 'suspendida');

        $this->iniciarSesion($this->rectorAlfa)->assertStatus(403)
            ->assertJsonPath('message', Usuario::mensajeDeBloqueo(Usuario::BLOQUEO_INSTITUCION));
        $this->assertGuest();
    }

    public function test_cookie_recordarme_de_institucion_suspendida_no_entra(): void
    {
        [$nombre, $valor] = $this->cookieRecordarme($this->docenteAlfa);
        $this->estado($this->alfa, 'suspendida');

        $this->withCookie($nombre, $valor)->get('/perfil')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_suspender_y_reactivar_desde_superadmin(): void
    {
        [$nombre, $valor] = $this->cookieRecordarme($this->docenteAlfa);
        $this->iniciarSesion($this->rectorAlfa)->assertOk();
        $sesionRector = $this->app['session']->all();
        $this->travel(5)->seconds();

        $this->nuevoNavegador();
        $this->actingAs($this->superadmin);
        $this->postJson("/superadmin/instituciones/{$this->alfa->id_institucion}/desactivar")->assertOk();
        $this->assertSame('suspendida', $this->alfa->fresh()->estado);
        $this->assertSame(0, DB::table('usuarios')->where('id_institucion', $this->alfa->id_institucion)->whereNotNull('remember_token')->count());
        // Otra institución intacta.
        $this->assertNull($this->rectorBeta->fresh()->sesion_valida_desde);

        $this->postJson("/superadmin/instituciones/{$this->alfa->id_institucion}/activar")->assertOk();
        $this->assertSame('activa', $this->alfa->fresh()->estado);

        // Reactivada: la sesión y la cookie anteriores NO reviven…
        $this->nuevoNavegador();
        $this->withSession($sesionRector)->get('/perfil')->assertRedirect(route('login'));
        $this->nuevoNavegador();
        $this->withCookie($nombre, $valor)->get('/perfil')->assertRedirect(route('login'));

        // …pero un login nuevo sí entra.
        $this->nuevoNavegador();
        $this->iniciarSesion($this->rectorAlfa)->assertOk();
        $this->get('/perfil')->assertOk();
    }

    public function test_superadmin_no_se_bloquea_por_instituciones(): void
    {
        $this->estado($this->alfa, 'suspendida');
        $this->estado($this->beta, 'inactiva');

        $this->actingAs($this->superadmin);
        $this->get('/perfil')->assertOk();
        $this->get('/superadmin/instituciones')->assertOk();
    }

    public function test_otra_institucion_activa_no_se_ve_afectada(): void
    {
        $this->estado($this->alfa, 'suspendida');

        $this->actingAs($this->rectorBeta);
        $this->get('/perfil')->assertOk();
        $this->get('/listados/estudiantes')->assertOk();
    }

    public function test_usuario_institucional_sin_institucion_queda_bloqueado_sin_error_500(): void
    {
        $huerfano = $this->usuario(2, null);

        $this->iniciarSesion($huerfano)->assertStatus(403);
        $this->actingAs($huerfano);
        $this->get('/perfil')->assertRedirect(route('login'));
    }

    public function test_flujos_publicos_siguen_disponibles_como_invitado(): void
    {
        $this->actingAs($this->rectorAlfa);
        $this->estado($this->alfa, 'suspendida');

        // Ruta pública: no redirige; continúa como invitado.
        $this->get('/')->assertOk();
        $this->assertGuest();
        $this->get('/login')->assertOk();
    }
}
