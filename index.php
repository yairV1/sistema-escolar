<?php

// index.php - Router principal

require_once __DIR__ . '/config/config.php';

// obtener la URL actual (por ejemplo: /nexus_center/login)

$requestURI = $_SERVER['REQUEST_URI'];

// Quitar le prefijo de la carpeta del proyecto

$request = str_replace('/colegio/', '', $requestURI);

// Quitar parametros tipo ?id=123

$request = strtok($request, '?');

// Quitar la barra final (si existe)

$request = rtrim($request, '/');

// Si la ruta queda vacia, se interpreta como "/"

if ($request === '') $request = '/';

// Entutamiento basico

switch ($request) {

    case '/':
        require BASE_PATH . '/app/views/webSite/index.php';
        break;

    case 'login':
        require BASE_PATH . '/app/views/auth/login.php';
        break;

    //Aca son todas las rutas para el administrativo desde el modelo, vista y controlador

    //ESTAS SON LAS VISTAS
    
    case 'inicio':
        require BASE_PATH . '/app/views/dashBoard/administracion/Inicio.php';
        break;

    case 'RegistroEstudiantes':
        require BASE_PATH . '/app/views/dashBoard/administracion/RegistroEstudiantes.php';
        break;

    case 'RegistroDocentes':
        require BASE_PATH . '/app/views/dashBoard/administracion/RegistroDocentes.php';
        break;

    case 'Listados':
        require BASE_PATH . '/app/views/dashBoard/administracion/Listados.php';
        break;

    case 'Matriculas':
        require BASE_PATH . '/app/views/dashBoard/administracion/Matriculas.php';
        break;

    case 'Estadisticas':
        require BASE_PATH . '/app/views/dashBoard/administracion/Estadisticas.php';
        break;

    case 'Reportes':
        require BASE_PATH . '/app/views/dashBoard/administracion/Reportes.php';
        break;

    case 'Comunicados':
        require BASE_PATH . '/app/views/dashBoard/administracion/Comunicados.php';
        break;

    case 'Observaciones':
        require BASE_PATH . '/app/views/dashBoard/administracion/Observaciones.php';
        break;

    case 'EditarLanding':
        require BASE_PATH . '/app/views/dashBoard/administracion/EditarLanding.php';
        break;

    case 'Perfil':
        require BASE_PATH . '/app/views/dashBoard/administracion/Perfil.php';
        break;
    //ESTAS SON LAS RUTAS PARA EL CONTROLADOR

    default:
        // Si la ruta no coincide con ninguna de las anteriores, mostrar un error 404
        http_response_code(404);
        echo "404 - Página no encontrada";
        break;
    
}