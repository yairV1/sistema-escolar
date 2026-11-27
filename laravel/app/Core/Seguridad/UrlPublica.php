<?php

namespace App\Core\Seguridad;

/**
 * URLs absolutas que salen del sistema (correos, enlaces de recuperación) a
 * partir de APP_URL —configuración del servidor—, nunca de la cabecera Host
 * de la petición (Fase 1, P0-1).
 *
 * APP_URL debe ser absoluta (http/https), sin usuario, contraseña, consulta ni
 * fragmento. En producción se exige https. Si no cumple, raiz() devuelve null
 * y el llamador debe fallar cerrado (no enviar el enlace).
 */
final class UrlPublica
{
    public static function raiz(): ?string
    {
        $url = trim((string) config('app.url'));
        $partes = $url === '' ? false : parse_url($url);

        if (! is_array($partes) || empty($partes['scheme']) || empty($partes['host'])
            || isset($partes['user']) || isset($partes['pass']) || isset($partes['query']) || isset($partes['fragment'])) {
            return null;
        }

        $esquema = strtolower($partes['scheme']);
        if (! in_array($esquema, ['http', 'https'], true)) {
            return null;
        }
        if ($esquema !== 'https' && app()->environment('production')) {
            return null;
        }

        return rtrim($url, '/');
    }

    /** Host de APP_URL (en minúsculas), o null si APP_URL no es válida. */
    public static function host(): ?string
    {
        $raiz = self::raiz();

        return $raiz === null ? null : strtolower((string) parse_url($raiz, PHP_URL_HOST));
    }

    /** @param  array<string, scalar>  $consulta */
    public static function a(string $ruta, array $consulta = []): ?string
    {
        $raiz = self::raiz();
        if ($raiz === null) {
            return null;
        }

        $url = $raiz.'/'.ltrim($ruta, '/');

        return $consulta === [] ? $url : $url.'?'.http_build_query($consulta, '', '&', PHP_QUERY_RFC3986);
    }
}
