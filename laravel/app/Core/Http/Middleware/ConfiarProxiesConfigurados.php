<?php

namespace App\Core\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Fase 1 (P1-4): proxies de confianza parametrizados por entorno, en lugar
 * del TrustProxies por defecto de Laravel (que reemplaza en bootstrap/app.php).
 *
 *  - Solo se aceptan X-Forwarded-* de las IPs/rangos de TRUSTED_PROXIES.
 *    Vacío = ninguno: IP, esquema y host son los de la conexión real.
 *  - '*', '**', 'REMOTE_ADDR' y rangos que abarcan todo Internet
 *    (0.0.0.0/0, ::/0) se descartan: confiar en todos permitiría falsear la
 *    IP (y con ella los límites de intentos), el esquema y el host.
 *  - Cabeceras aceptadas: For, Proto y Port. X-Forwarded-Host solo con
 *    TRUSTED_PROXY_FORWARDED_HOST=true (y aun así lo filtra ValidarHostConfiable).
 */
class ConfiarProxiesConfigurados extends TrustProxies
{
    private const PROHIBIDOS = ['*', '**', 'remote_addr', '0.0.0.0/0', '::/0'];

    protected function proxies()
    {
        ['validos' => $validos, 'ignorados' => $ignorados] = self::clasificar((array) config('seguridad.proxies_confiables', []));

        if ($ignorados !== []) {
            Log::warning('TRUSTED_PROXIES contiene entradas no permitidas y se ignoraron.', ['entradas' => $ignorados]);
        }

        return $validos;
    }

    /**
     * Separa las entradas aceptables de las que nunca se aceptan. También la
     * usa el comando seguridad:verificar-exposicion.
     *
     * @param  array<int, string>  $entradas
     * @return array{validos: array<int, string>, ignorados: array<int, string>}
     */
    public static function clasificar(array $entradas): array
    {
        $validos = [];
        $ignorados = [];
        foreach ($entradas as $entrada) {
            $entrada = strtolower(trim((string) $entrada));
            if ($entrada === '') {
                continue;
            }
            if (in_array($entrada, self::PROHIBIDOS, true) || ! self::esIpOCidrValido($entrada)) {
                $ignorados[] = $entrada;

                continue;
            }
            $validos[] = $entrada;
        }

        return ['validos' => $validos, 'ignorados' => $ignorados];
    }

    /**
     * Sustituye la lógica de la clase base, que con lista vacía cae a
     * config('trustedproxy.proxies') y, según el Host recibido (*.on-forge.com,
     * *.on-vapor.com), termina confiando en cualquier IP. Aquí: solo la lista
     * validada; vacía = ningún proxy.
     */
    protected function setTrustedProxyIpAddresses(Request $request)
    {
        $proxies = $this->proxies();

        if ($proxies === []) {
            $request::setTrustedProxies([], $this->getTrustedHeaderNames());

            return;
        }

        $this->setTrustedProxyIpAddressesToSpecificIps($request, $proxies);
    }

    protected function headers()
    {
        $cabeceras = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PORT;

        if (config('seguridad.confiar_host_reenviado')) {
            $cabeceras |= Request::HEADER_X_FORWARDED_HOST;
        }

        return $cabeceras;
    }

    private static function esIpOCidrValido(string $valor): bool
    {
        [$ip, $mascara] = array_pad(explode('/', $valor, 2), 2, null);
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        if ($mascara === null) {
            return true;
        }
        $max = str_contains($ip, ':') ? 128 : 32;

        return ctype_digit($mascara) && (int) $mascara >= 8 && (int) $mascara <= $max;
    }
}
