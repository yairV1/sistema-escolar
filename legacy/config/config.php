<?php

// Este archivo se creo para crear menor complicacion al momento de subier el proyecto a un hosting

// ═══════════════════════════════════════════════════════════════════════════
//  CONFIGURACIÓN DE ZONA HORARIA
// ═══════════════════════════════════════════════════════════════════════════
// Establece la zona horaria para evitar problemas con fechas y horas
// Ajusta según tu ubicación: América/Bogotá, América/Mexico_City, etc.
date_default_timezone_set('America/Bogota');

// Configuracion global del proyecto

// Detectar protocolo (http o https)

$protocolo = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';

// nombre de la carpeta del proyecto en local

$baseFolder = '/colegio/legacy/'; // Cambia esto si tu carpeta tiene otro nombre o si lo subes a la raíz del hosting

// Host casual

$host = $_SERVER['HTTP_HOST'];

// url base dianmica (funcion en local y hosting)

define('BASE_URL', $protocolo . $host . $baseFolder);

// URL del sistema Laravel (hermano de este legacy dentro de la misma
// carpeta del proyecto, ej. colegio/legacy/ y colegio/laravel/). Se
// deriva del padre de $baseFolder en vez de hardcodear "/colegio/" de
// nuevo, para no duplicar ese acoplamiento en dos lugares.
$parentFolder = rtrim(dirname(rtrim($baseFolder, '/')), '/') . '/';
define('LARAVEL_URL', $protocolo . $host . $parentFolder . 'laravel/public/');

// Ruta base del proyecto (para require o include)

define('BASE_PATH', dirname(__DIR__));

// ═══════════════════════════════════════════════════════════════════════════
//  SESIÓN (autenticación de usuarios)
// ═══════════════════════════════════════════════════════════════════════════
// Se inicia aquí, en el único archivo que cargan tanto index.php como todas
// las vistas, para garantizar que $_SESSION esté disponible siempre.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $baseFolder,
        'domain'   => '',
        'secure'   => $protocolo === 'https://',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once BASE_PATH . '/app/helpers/Auth.php';
