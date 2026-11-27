<?php

namespace App\Core\Seguridad\Console;

use App\Core\Http\Middleware\ConfiarProxiesConfigurados;
use App\Core\Seguridad\UrlPublica;
use Illuminate\Console\Command;

/**
 * Fase 1 (P0-2/P1-4): compuerta previa a exponer el sistema (servidor público
 * o túnel ngrok). Solo LEE la configuración efectiva; no cambia nada.
 * Código de salida 1 si hay algún bloqueante: la exposición queda BLOQUEADA
 * hasta corregirlos. No muestra valores secretos.
 *
 *   php artisan seguridad:verificar-exposicion
 *   php artisan seguridad:verificar-exposicion --ngrok
 */
class VerificarExposicionCommand extends Command
{
    protected $signature = 'seguridad:verificar-exposicion {--ngrok : Exige además la configuración de proxy local que necesita ngrok}';

    protected $description = 'Verifica (solo lectura) si la configuración permite exponer el sistema fuera de esta máquina.';

    public function handle(): int
    {
        $filas = [];
        $bloqueantes = 0;
        $agregar = function (string $control, bool $ok, string $detalle, bool $bloqueante = true) use (&$filas, &$bloqueantes) {
            $estado = $ok ? 'OK' : ($bloqueante ? 'BLOQUEANTE' : 'AVISO');
            if (! $ok && $bloqueante) {
                $bloqueantes++;
            }
            $filas[] = [$control, $estado, $detalle];
        };

        $agregar('APP_ENV', app()->environment('production'), 'Debe ser production (actual: '.app()->environment().').');
        $agregar('APP_DEBUG', ! (bool) config('app.debug'), 'Debe ser false: con true las páginas de error muestran código y configuración.');
        $agregar('APP_KEY', filled(config('app.key')), 'Debe estar definida (no se muestra).');

        $raiz = UrlPublica::raiz();
        $https = $raiz !== null && str_starts_with(strtolower($raiz), 'https://');
        $agregar('APP_URL', $raiz !== null && $https, 'Debe ser la URL pública exacta con https (base de los enlaces de recuperación).');

        $validar = config('seguridad.validar_hosts');
        $validaHosts = $validar === null ? ! app()->environment(['local', 'testing']) : (bool) $validar;
        $agregar('Validación de Host', $validaHosts, 'Debe estar activa (APP_VALIDATE_HOSTS vacío con APP_ENV=production, o true).');

        $hosts = array_values(array_unique(array_filter(array_merge((array) config('seguridad.hosts_confiables', []), [UrlPublica::host()]))));
        $agregar('Hosts aceptados', $hosts !== [], $hosts === [] ? 'Ninguno: definir APP_URL válida.' : implode(', ', $hosts));

        ['validos' => $proxies, 'ignorados' => $ignorados] = ConfiarProxiesConfigurados::clasificar((array) config('seguridad.proxies_confiables', []));
        $agregar('TRUSTED_PROXIES (entradas)', $ignorados === [], $ignorados === [] ? 'Sin entradas prohibidas.' : 'Se ignoran: '.implode(', ', $ignorados).' (nunca «todos»).');
        if ($this->option('ngrok')) {
            $local = array_intersect($proxies, ['127.0.0.1', '::1']) !== [];
            $agregar('Proxy ngrok local', $local, 'ngrok en esta máquina: TRUSTED_PROXIES="127.0.0.1,::1" (IP real del cliente para los límites).');
        } else {
            $agregar('TRUSTED_PROXIES', true, $proxies === [] ? 'Ninguno: correcto solo si los clientes se conectan directo a este servidor.' : implode(', ', $proxies));
        }
        $agregar('X-Forwarded-Host', ! config('seguridad.confiar_host_reenviado'), 'Se recomienda no aceptarlo (TRUSTED_PROXY_FORWARDED_HOST=false).', false);

        $agregar('SESSION_SECURE_COOKIE', (bool) config('session.secure'), 'Debe ser true: la cookie de sesión solo viaja por https.');
        $agregar('LOG_LEVEL', config('logging.channels.'.config('logging.default').'.level', 'debug') !== 'debug'
            && config('logging.channels.single.level', 'debug') !== 'debug', 'Se recomienda info o warning en un entorno expuesto.', false);

        $this->table(['Control', 'Estado', 'Detalle'], $filas);

        if ($bloqueantes > 0) {
            $this->error("Exposición BLOQUEADA: {$bloqueantes} control(es) bloqueante(s). No publiques el sistema hasta corregirlos.");

            return self::FAILURE;
        }

        $this->info('Sin bloqueantes de configuración. Falta validar la topología real del proxy (docs/seguridad/fase1-contencion.md).');

        return self::SUCCESS;
    }
}
