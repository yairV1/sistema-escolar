<?php

namespace Tests\Feature\SuperAdmin;

use App\Core\Seguridad\TwoFactorService;
use Illuminate\Support\Facades\Hash;

class TwoFactorLoginTest extends SuperAdminTestCase
{
    public function test_login_con_2fa_activo_no_autentica_hasta_verificar_el_codigo(): void
    {
        $twoFactor = app(TwoFactorService::class);
        $secreto = $twoFactor->generarSecreto();

        $superadmin = $this->crearSuperAdmin();
        $superadmin->forceFill([
            'password' => Hash::make('password123'),
            'two_factor_secret' => $secreto,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $login = $this->postJson('/login', [
            'usuario' => $superadmin->correo,
            'password' => 'password123',
        ]);

        $login->assertOk()->assertJsonPath('redirect', route('2fa.challenge.show'));
        $this->assertGuest();

        $codigoIncorrecto = $this->postJson('/2fa', ['codigo' => '000000']);
        $codigoIncorrecto->assertStatus(401);
        $this->assertGuest();

        $codigoValido = $this->generarCodigoValido($twoFactor, $secreto);

        $verificacion = $this->postJson('/2fa', ['codigo' => $codigoValido]);
        $verificacion->assertOk()->assertJson(['success' => true]);
        $this->assertAuthenticatedAs($superadmin->fresh());
    }

    private function generarCodigoValido(TwoFactorService $twoFactor, string $secreto): string
    {
        // TwoFactorService::verificar prueba +/-1 paso contra el reloj real;
        // el código válido "ahora" se obtiene probando los 6 dígitos que el
        // propio verificar() aceptaría, generándolo con el mismo algoritmo
        // vía reflexión ya que generarCodigo() es privado por diseño.
        $reflexion = new \ReflectionMethod($twoFactor, 'generarCodigo');
        $paso = intdiv(time(), 30);

        return $reflexion->invoke($twoFactor, $secreto, $paso);
    }
}
