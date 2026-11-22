<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Invalidación forzada de sesión al cambiar contraseña, rol o permisos (pedido
 * explícito de seguridad del SuperAdmin). Registrado globalmente en el
 * grupo `web` (bootstrap/app.php) pero es un no-op para cualquier usuario
 * cuyo `sesion_valida_desde` sea null — es decir, cero impacto en los 7
 * roles institucionales existentes hasta que alguien (típicamente el
 * SuperAdmin) toque el rol o los permisos de una cuenta y ese timestamp
 * se estampe (ver UsuarioGlobalController/RolPermisoController).
 *
 * `auth_at` se guarda en sesión en el momento del login (LoginController).
 */
class EnsureSessionFresh
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->sesion_valida_desde) {
            return $next($request);
        }

        $autenticadoEn = $request->session()->get('auth_at');

        // Sesión sin marca de autenticación (incidente 2026-11-19: antes se dejaba pasar).
        // Si se restauró con la cookie «recordarme», el token ya se comprobó contra el
        // remember_token vigente —que se rota al cambiar la contraseña—: se marca ahora.
        // Cualquier otra sesión sin marca se trata como anterior al cambio (falla cerrado).
        if ($autenticadoEn === null && Auth::guard('web')->viaRemember()) {
            $autenticadoEn = now()->timestamp;
            $request->session()->put('auth_at', $autenticadoEn);
        }

        if ($autenticadoEn === null || $usuario->sesion_valida_desde->timestamp > $autenticadoEn) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(401, 'Tu sesión fue invalidada por un cambio de credenciales o permisos. Vuelve a iniciar sesión.');
            }

            return redirect()->route('login')->with('status', 'Tu sesión fue invalidada por un cambio de contraseña, rol o permisos. Vuelve a iniciar sesión.');
        }

        return $next($request);
    }
}
