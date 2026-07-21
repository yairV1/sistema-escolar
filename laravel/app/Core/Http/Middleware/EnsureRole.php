<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Equivalente a Auth::requiereRol()/requiereRolApi() del sistema legacy:
 * exige sesión autenticada y que el rol del usuario esté en la lista
 * permitida. Uso: ->middleware('role:admin,rector')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$rolesPermitidos): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            if ($request->expectsJson()) {
                abort(401, 'No autenticado.');
            }

            return redirect()->guest(route('login'));
        }

        if (! in_array($usuario->rolSlug, $rolesPermitidos, true)) {
            abort(403, 'No tienes permisos para acceder a este recurso.');
        }

        return $next($request);
    }
}
