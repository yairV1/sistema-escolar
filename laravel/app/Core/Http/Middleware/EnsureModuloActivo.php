<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso a una funcionalidad cuyo módulo (catálogo comercial,
 * ver Institucion::tieneModuloActivo) no está activo para la institución
 * del usuario autenticado. Uso: ->middleware('modulo:boletines')
 */
class EnsureModuloActivo
{
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $usuario = $request->user();
        $institucion = $usuario?->institucion;

        if ($institucion && ! $institucion->tieneModuloActivo($slug)) {
            abort(403, 'Este módulo no está disponible en el plan de tu institución.');
        }

        return $next($request);
    }
}
