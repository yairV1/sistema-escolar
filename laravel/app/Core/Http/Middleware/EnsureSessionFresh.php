<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Invalidación forzada de sesión al cambiar rol o permisos (pedido
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

        if ($autenticadoEn !== null && $usuario->sesion_valida_desde->timestamp > $autenticadoEn) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(401, 'Tu sesión fue invalidada por un cambio de permisos. Vuelve a iniciar sesión.');
            }

            return redirect()->route('login')->with('status', 'Tu sesión fue invalidada por un cambio de rol o permisos. Vuelve a iniciar sesión.');
        }

        return $next($request);
    }
}
