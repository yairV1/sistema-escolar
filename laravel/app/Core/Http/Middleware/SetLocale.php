<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica el idioma preferido del usuario logueado (usuarios.idioma) al
 * locale de la request. No-op para visitantes sin sesión (ej. login): ahí
 * no hay preferencia que leer todavía, así que esas pantallas quedan en el
 * locale por defecto de la app.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($usuario = $request->user()) {
            app()->setLocale($usuario->idioma ?: 'es');
        }

        return $next($request);
    }
}
