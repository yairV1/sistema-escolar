<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Seguridad\TwoFactorService;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\SuperAdmin\Requests\TwoFactorConfirmRequest;
use App\Modules\SuperAdmin\Requests\TwoFactorDisableRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Autoservicio de 2FA para la propia cuenta SuperAdmin autenticada (no
 * gestiona 2FA de otras cuentas). El secreto generado en enable() vive
 * solo en sesión hasta confirm() — nunca se persiste sin verificar que el
 * usuario realmente lo registró en su app autenticadora.
 */
class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorService $twoFactor, private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        return view('SuperAdmin.dosfactores.index', [
            'currentPage' => 'SuperAdminDosFactores',
            'activo' => $request->user()->tieneDosFactoresActivos(),
        ]);
    }

    public function enable(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if ($usuario->tieneDosFactoresActivos()) {
            return response()->json(['success' => false, 'message' => 'Ya tienes 2FA activo.'], 422);
        }

        $secreto = $this->twoFactor->generarSecreto();
        $request->session()->put('2fa_setup.secreto', $secreto);

        $uri = $this->twoFactor->provisioningUri($secreto, $usuario->correo);

        return response()->json([
            'success' => true,
            'secreto' => $secreto,
            'qr_svg' => $this->twoFactor->qrSvg($uri),
        ]);
    }

    public function confirm(TwoFactorConfirmRequest $request): JsonResponse
    {
        $secreto = $request->session()->get('2fa_setup.secreto');

        if (! $secreto) {
            return response()->json(['success' => false, 'message' => 'Primero genera un código QR.'], 422);
        }

        if (! $this->twoFactor->verificar($secreto, $request->string('codigo'))) {
            return response()->json(['success' => false, 'message' => 'Código incorrecto.'], 422);
        }

        $codigosRecuperacion = $this->twoFactor->generarCodigosRecuperacion();

        $usuario = $request->user();
        $usuario->forceFill([
            'two_factor_secret' => $secreto,
            'two_factor_recovery_codes' => $codigosRecuperacion,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('2fa_setup.secreto');
        $this->auditLogger->record('auth.2fa.activar', $usuario);

        return response()->json([
            'success' => true,
            'message' => '2FA activado correctamente.',
            'codigos_recuperacion' => $codigosRecuperacion,
        ]);
    }

    public function disable(TwoFactorDisableRequest $request): JsonResponse
    {
        $usuario = $request->user();

        $usuario->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->auditLogger->record('auth.2fa.desactivar', $usuario);

        return response()->json(['success' => true, 'message' => '2FA desactivado.']);
    }

    public function regenerarCodigosRecuperacion(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if (! $usuario->tieneDosFactoresActivos()) {
            return response()->json(['success' => false, 'message' => 'Activa 2FA primero.'], 422);
        }

        $codigos = $this->twoFactor->generarCodigosRecuperacion();
        $usuario->forceFill(['two_factor_recovery_codes' => $codigos])->save();

        $this->auditLogger->record('auth.2fa.regenerar_codigos', $usuario);

        return response()->json(['success' => true, 'codigos_recuperacion' => $codigos]);
    }
}
