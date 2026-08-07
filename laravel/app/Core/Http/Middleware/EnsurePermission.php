<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Primera implementación real de `PermissionMiddleware`, diseñado pero
 * nunca implementado en docs/arquitectura/03-rbac.md §7.2. Se usa
 * exclusivamente en las rutas nuevas de /superadmin/*; ninguna ruta
 * existente (role:admin,rector, etc.) se migra a este middleware como
 * parte de este cambio — esa migración gradual es la Fase 3 completa,
 * documentada como pendiente en el propio 03-rbac.md §9.
 *
 * Uso: ->middleware('permission:plataforma.instituciones.ver')
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            if ($request->expectsJson()) {
                abort(401, 'No autenticado.');
            }

            return redirect()->guest(route('login'));
        }

        if (! $usuario->hasPermission($permiso)) {
            abort(403, 'No tienes el permiso requerido para esta acción.');
        }

        return $next($request);
    }
}
