<?php

/**
 * URL base del sistema PHP legacy (colegio/legacy/, movido ahí en la
 * reorganización de la raíz del repositorio), que convive con Laravel
 * durante la migración incremental. Se usa para armar los links del
 * sidebar hacia módulos que todavía no se han migrado.
 */
return [
    'url' => env('LEGACY_URL', 'http://localhost/colegio/legacy/'),
];
