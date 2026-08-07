<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Evita que el navegador sirva páginas del panel desde su caché (incluido el
 * back-forward cache) después de cerrar sesión: sin estas cabeceras, el
 * botón "Atrás" del navegador muestra el panel con datos reales aunque la
 * sesión ya se invalidó en el servidor (repro: login -> logout -> Atrás).
 */
class PreventCachedResponses
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
