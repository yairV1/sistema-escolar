<?php

namespace App\Core\Http\Middleware;

use App\Modules\Instituciones\Support\TenantGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aislamiento por institución de TODOS los modelos resueltos desde la URL
 * (route model binding). Se agrega al grupo "web" después de
 * SubstituteBindings, de modo que ninguna ruta —presente o futura— puede
 * olvidarlo.
 *
 * - Usuario institucional: cada parámetro que use PerteneceAInstitucion debe
 *   pertenecer a su institución; si no, o si no puede determinarse, 404.
 * - SuperAdmin: exento. Opera la plataforma (instituciones, usuarios
 *   globales, soporte) y las rutas académicas ya se lo niegan por rol, así
 *   que esto no le concede acceso académico.
 * - Invitado: sin cambios (las rutas con token propio, p. ej. calendario ICS,
 *   tienen su propia verificación; el resto exige auth en la ruta).
 */
class EnsureRecursosDeInstitucion
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();
        $ruta = $request->route();

        if ($usuario && $ruta && ! $usuario->esSuperAdmin()) {
            foreach ($ruta->parameters() as $parametro) {
                if (TenantGuard::esInstitucional($parametro)) {
                    TenantGuard::asegurar($parametro);
                }
            }
        }

        return $next($request);
    }
}
