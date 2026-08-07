<?php

namespace App\Modules\Auth\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Seguridad\TwoFactorService;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Auth\Requests\TwoFactorChallengeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Segundo paso del login cuando la cuenta tiene 2FA confirmado
 * (Usuario::tieneDosFactoresActivos). LoginController::store() no llama
 * Auth::login() en ese caso: deja el id de usuario pendiente en sesión y
 * redirige acá. La sesión pendiente NO es una sesión autenticada — Auth::user()
 * sigue siendo null hasta que el código se verifica.
 */
class TwoFactorChallengeController extends Controller
{
    public function __construct(private TwoFactorService $twoFactor, private AuditLogger $auditLogger) {}

    public function show(Request $request): View
    {
        if (! $request->session()->has('two_factor.id_usuario')) {
            abort(404);
        }

        return view('auth.two-factor-challenge');
    }

    public function store(TwoFactorChallengeRequest $request): JsonResponse
    {
        $idUsuario = $request->session()->get('two_factor.id_usuario');

        if (! $idUsuario) {
            return response()->json(['success' => false, 'message' => 'Tu sesión de verificación expiró. Inicia sesión de nuevo.'], 419);
        }

        $usuario = Usuario::find($idUsuario);

        if (! $usuario || ! $usuario->tieneDosFactoresActivos()) {
            return response()->json(['success' => false, 'message' => 'No se pudo verificar la cuenta.'], 422);
        }

        $codigo = trim($request->string('codigo'));
        $valido = $this->twoFactor->verificar($usuario->two_factor_secret, $codigo)
            || $this->consumirCodigoRecuperacion($usuario, $codigo);

        if (! $valido) {
            $this->auditLogger->record('auth.2fa.codigo_invalido', $usuario);

            return response()->json(['success' => false, 'message' => 'Código incorrecto o expirado.'], 401);
        }

        $recordar = (bool) $request->session()->pull('two_factor.remember', false);
        $request->session()->forget('two_factor.id_usuario');

        Auth::login($usuario, $recordar);
        $request->session()->regenerate();
        $request->session()->put('auth_at', now()->timestamp);
        $usuario->forceFill(['ultimo_acceso' => now()])->saveQuietly();

        $this->auditLogger->record('auth.2fa.verificado', $usuario);

        return response()->json([
            'success' => true,
            'message' => '¡Bienvenido! Redirigiendo...',
            'redirect' => $usuario->esSuperAdmin() ? route('superadmin.dashboard') : route('inicio'),
        ]);
    }

    private function consumirCodigoRecuperacion(Usuario $usuario, string $codigo): bool
    {
        $codigos = $usuario->two_factor_recovery_codes ?? [];
        $codigo = strtoupper($codigo);

        if (! in_array($codigo, $codigos, true)) {
            return false;
        }

        $usuario->forceFill([
            'two_factor_recovery_codes' => array_values(array_diff($codigos, [$codigo])),
        ])->saveQuietly();

        return true;
    }
}
