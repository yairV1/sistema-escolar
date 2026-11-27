<?php

/*
|--------------------------------------------------------------------------
| Seguridad de exposición (Fase 1 — contención)
|--------------------------------------------------------------------------
|
| Ver docs/seguridad/fase1-contencion.md. Todo se configura por entorno; los
| valores por defecto son los seguros para un servidor accesible desde fuera
| y no cambian el desarrollo local (APP_ENV=local).
|
*/

$lista = static fn (?string $valor): array => array_values(array_filter(array_map(
    static fn (string $item) => strtolower(trim($item)),
    explode(',', (string) $valor),
), static fn (string $item) => $item !== ''));

return [

    /*
    | Hosts aceptados en la cabecera Host (además del host de APP_URL, que
    | siempre se acepta). Nombres exactos, sin comodines ni subdominios
    | implícitos. Ej.: APP_TRUSTED_HOSTS="colegio.example.org,www.colegio.example.org"
    */
    'hosts_confiables' => $lista(env('APP_TRUSTED_HOSTS')),

    /*
    | Validar la cabecera Host. null = automático: activo salvo en APP_ENV
    | local o testing. En cualquier entorno accesible desde fuera debe quedar
    | activo (true o automático con APP_ENV=production).
    */
    // Vacío, ausente o no booleano = automático (null): un valor mal escrito
    // nunca desactiva la validación. Solo true/false explícitos la fijan.
    'validar_hosts' => (static function ($valor) {
        if (is_bool($valor)) {
            return $valor;
        }

        return in_array(trim((string) $valor), ['', 'null'], true)
            ? null
            : filter_var($valor, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    })(env('APP_VALIDATE_HOSTS')),

    /*
    | Proxies cuyas cabeceras X-Forwarded-* se aceptan: IPs o rangos CIDR
    | exactos. Vacío = ninguno (la IP del cliente es la de la conexión).
    | '*', '**' y rangos que cubren todo Internet se ignoran a propósito.
    | ngrok en la misma máquina: TRUSTED_PROXIES="127.0.0.1,::1"
    */
    'proxies_confiables' => $lista(env('TRUSTED_PROXIES')),

    /*
    | Aceptar X-Forwarded-Host de un proxy confiable. Desactivado por defecto:
    | ngrok y la mayoría de proxies conservan el Host original, y aceptar el
    | reenviado amplía la superficie de manipulación de URLs.
    */
    'confiar_host_reenviado' => filter_var(env('TRUSTED_PROXY_FORWARDED_HOST', false), FILTER_VALIDATE_BOOL),

];
