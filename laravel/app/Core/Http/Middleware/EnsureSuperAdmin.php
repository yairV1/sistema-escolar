<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Capa de autorización separada de EnsureRole a propósito (pedido
 * explícito del diseño: "no reutilizar el mismo guard/middleware sin
 * distinción" — ver docs/arquitectura/10-superadmin-plataforma.md). Un
 * SuperAdmin comprometido o un bug en esta capa nunca debe poder afectar
 * la autorización del panel institucional, ni viceversa.
 *
 * Uso: ->middleware('superadmin'), sin parámetros — a diferencia de
 * `role:...`, SuperAdmin no es una lista, es un único nivel de acceso.
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            if ($request->expectsJson()) {
                abort(401, 'No autenticado.');
            }

            return redirect()->guest(route('login'));
        }

        if (! $usuario->esSuperAdmin() || ! $usuario->estaActivo()) {
            abort(403, 'No tienes permisos para acceder a este recurso.');
        }

        return $next($request);
    }
}
