<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $usuario = Auth::user();

        if ($usuario && $usuario->tienePanelAdmin()) {
            return redirect()->route('inicio');
        }

        return view('auth.login');
    }

    /**
     * Acepta correo o número de documento en un solo campo (igual que el
     * legacy): se detecta el tipo con FILTER_VALIDATE_EMAIL. El mensaje de
     * error es idéntico si el usuario no existe o si el password no
     * coincide, para no filtrar qué cuentas existen.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $identificador = trim($request->string('usuario'));

        $usuario = filter_var($identificador, FILTER_VALIDATE_EMAIL)
            ? Usuario::where('correo', $identificador)->first()
            : Usuario::where('numero_documento', $identificador)->first();

        if (! $usuario || ! Hash::check($request->string('password'), $usuario->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos.',
            ], 401);
        }

        if (! $usuario->estaActivo()) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta se encuentra inactiva o bloqueada. Contacta al colegio.',
            ], 403);
        }

        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();
        $usuario->forceFill(['ultimo_acceso' => now()])->saveQuietly();

        return response()->json([
            'success' => true,
            'message' => '¡Bienvenido! Redirigiendo...',
            'redirect' => $usuario->tienePanelAdmin() ? route('inicio') : url('/'),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
