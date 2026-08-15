<?php

/**
 * Shim de front controller: expone Laravel en /colegio/ sin el prefijo
 * /laravel/public/, sin modificar laravel/public/index.php (estructura
 * interna de Laravel intacta).
 *
 * El .htaccess de esta carpeta reescribe internamente cualquier ruta que
 * no sea un archivo real ni empiece con /legacy/ hacia este archivo.
 * REQUEST_URI llega sin tocar (ej. /colegio/login), pero SCRIPT_NAME
 * apuntaría por defecto a la ruta física real (laravel/public/index.php),
 * y Laravel/Symfony usa SCRIPT_NAME para calcular la ruta interna — con
 * ese desfase, cualquier URL devuelve 404. Corregimos SCRIPT_NAME acá
 * para que coincida con la URL pública antes de cargar Laravel.
 */

$_SERVER['SCRIPT_NAME'] = '/sistema-escolar/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/laravel/public/index.php';

chdir(__DIR__ . '/laravel/public');
require __DIR__ . '/laravel/public/index.php';
