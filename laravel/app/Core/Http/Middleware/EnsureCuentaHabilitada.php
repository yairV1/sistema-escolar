<?php

namespace App\Core\Http\Middleware;

use App\Modules\Auth\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fase 1 (P1-1/P1-2): una cuenta que ya no puede operar —usuario inactivo o
 * bloqueado, rol inactivo, institución suspendida/inactiva— pierde el acceso
 * en su siguiente petición, tanto si la sesión viene de la sesión del
 * servidor como si se restauró con una cookie «recordarme».
 *
 * Va en el grupo `web` (bootstrap/app.php), así que cubre el panel
 * institucional, las rutas solo-`auth` (perfil, calendario, notificaciones,
 * soporte), broadcasting/auth y SuperAdmin. La regla vive en
 * Usuario::motivoDeBloqueo(); EnsureSuperAdmin conserva su propia
 * comprobación (capa separada).
 *
 * Al bloquear: logout (borra la sesión y la cookie «recordarme» y rota su
 * token), sesión invalidada y token CSRF nuevo. En rutas públicas la petición
 * sigue como invitado; en rutas protegidas se responde 401 (JSON) o se
 * redirige al login con el motivo.
 */
class EnsureCuentaHabilitada
{
    /** Middleware de ruta que indican que la ruta exige una cuenta. */
    private const PROTECCIONES = ['auth', 'superadmin', 'role', 'permission'];

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');

        /** @var Usuario|null $usuario */
        $usuario = $guard->user();
        if ($usuario === null) {
            return $next($request);
        }

        $motivo = $usuario->motivoDeBloqueo();
        if ($motivo === null) {
            return $next($request);
        }

        $guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (! $this->rutaProtegida($request)) {
            return $next($request);
        }

        $mensaje = Usuario::mensajeDeBloqueo($motivo);

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $mensaje], 401);
        }

        return redirect()->route('login')->with('status', $mensaje);
    }

    private function rutaProtegida(Request $request): bool
    {
        $ruta = $request->route();
        if ($ruta === null) {
            return true;   // sin ruta resuelta: falla cerrado
        }

        foreach ($ruta->gatherMiddleware() as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }
            $nombre = strtolower(strtok($middleware, ':'));
            if (in_array($nombre, self::PROTECCIONES, true) || str_ends_with($nombre, '\\authenticate')) {
                return true;
            }
        }

        return false;
    }
}
