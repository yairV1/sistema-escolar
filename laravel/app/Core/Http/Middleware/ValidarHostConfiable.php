<?php

namespace App\Core\Http\Middleware;

use App\Core\Seguridad\UrlPublica;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fase 1 (P0-1/P1-4): rechaza peticiones cuya cabecera Host no es uno de los
 * hosts del servidor (host de APP_URL + APP_TRUSTED_HOSTS). Sin esto, cualquier
 * URL generada con url()/route() —notificaciones, redirecciones, enlaces—
 * puede apuntar a un dominio elegido por quien hace la petición.
 *
 * Activo según config('seguridad.validar_hosts'): automático = todo entorno
 * salvo local y testing. Nombres exactos, sin subdominios implícitos.
 * Responde 400 sin revelar la lista de hosts.
 */
class ValidarHostConfiable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->debeValidar()) {
            return $next($request);
        }

        $permitidos = $this->hostsPermitidos();
        // Host de la petición sin puerto; getHost() ya valida el formato.
        $host = strtolower((string) $request->getHost());

        if ($permitidos === [] || ! in_array($host, $permitidos, true)) {
            return response('Solicitud no válida.', 400, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return $next($request);
    }

    private function debeValidar(): bool
    {
        $config = config('seguridad.validar_hosts');

        return $config === null ? ! app()->environment(['local', 'testing']) : (bool) $config;
    }

    /** @return array<int, string> */
    private function hostsPermitidos(): array
    {
        $hosts = (array) config('seguridad.hosts_confiables', []);
        if ($host = UrlPublica::host()) {
            $hosts[] = $host;
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($h) => strtolower(trim((string) $h)),
            $hosts,
        ), fn ($h) => $h !== '' && ! str_contains($h, '*'))));
    }
}
