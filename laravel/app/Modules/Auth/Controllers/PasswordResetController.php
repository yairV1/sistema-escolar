<?php

namespace App\Modules\Auth\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Seguridad\UrlPublica;
use App\Modules\Auth\Mail\RecuperarPasswordMail;
use App\Modules\Auth\Models\PasswordReset;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Auth\Requests\ForgotPasswordRequest;
use App\Modules\Auth\Requests\ResetPasswordRequest;
use App\Modules\Auth\Services\CambioDeContrasena;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'token' => trim((string) $request->query('token', '')),
        ]);
    }

    /**
     * Siempre responde el mismo mensaje de éxito, exista o no la cuenta,
     * para no permitir enumerar correos registrados (igual que el legacy).
     */
    public function storeForgot(ForgotPasswordRequest $request): JsonResponse
    {
        $usuario = Usuario::where('correo', $request->string('correo'))->first();

        if ($usuario && $usuario->estaActivo()) {
            // Fase 1 (P0-1): dominio del enlace = APP_URL validada, nunca el Host de
            // la petición. Si APP_URL no es válida (o no es https en producción), no
            // se genera token ni se envía nada: falla cerrado.
            if (UrlPublica::raiz() === null) {
                Log::critical('Recuperación de contraseña sin enviar: APP_URL no es una URL pública válida para este entorno.');
            } else {
                $token = PasswordReset::generarPara($usuario);
                $enlace = UrlPublica::a('reset-password', ['token' => $token]);

                Mail::to($usuario->correo)->send(new RecuperarPasswordMail($usuario, $enlace));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación en unos minutos.',
        ]);
    }

    public function storeReset(ResetPasswordRequest $request, CambioDeContrasena $cambio): JsonResponse
    {
        $reset = PasswordReset::validoPorToken($request->string('token'));

        if (! $reset) {
            return response()->json([
                'success' => false,
                'message' => 'El enlace no es válido o ya expiró. Solicita uno nuevo.',
            ], 400);
        }

        $usuario = Usuario::find($reset->id_usuario);

        // Fase 1 (P1-1/P1-2): un enlace emitido antes de desactivar la cuenta o
        // suspender la institución ya no sirve. Mismo mensaje que un token inválido.
        if (! $usuario || ! $usuario->estaActivo()) {
            return response()->json([
                'success' => false,
                'message' => 'El enlace no es válido o ya expiró. Solicita uno nuevo.',
            ], 400);
        }

        // Auditado + invalida todas las sesiones y cookies «recordarme» (incidente 2026-11-19).
        $cambio->aplicar($usuario, (string) $request->string('password'), CambioDeContrasena::ORIGEN_RECUPERACION);
        $reset->marcarUsado();

        return response()->json([
            'success' => true,
            'message' => 'Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.',
        ]);
    }
}
