<?php

// index.php - Router principal

require_once __DIR__ . '/config/config.php';

// obtener la URL actual (por ejemplo: /nexus_center/login)

$requestURI = $_SERVER['REQUEST_URI'];

// Quitar el prefijo de la carpeta del proyecto (mismo valor que
// config.php usa para BASE_URL, para no duplicar el hardcodeo en 2 lugares)

$request = str_replace($baseFolder, '', $requestURI);

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
        // Se mantiene activo (a diferencia de 'inicio') porque Laravel y el
        // sistema legacy tienen sesiones independientes: mientras el resto
        // del panel (Listados, Matrículas, etc.) no esté migrado, este login
        // sigue siendo necesario para poder acceder a esas páginas.
        if (Auth::estaAutenticado() && in_array(Auth::rol(), Auth::ROLES_PANEL_ADMIN, true)) {
            header('Location: ' . BASE_URL . 'inicio');
            break;
        }
        require BASE_PATH . '/app/views/auth/login.php';
        break;

    case 'logout':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->logout();
        break;

    case 'api/auth/login':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->login();
        break;

    case 'api/auth/recuperar':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->solicitarRecuperacion();
        break;

    case 'api/auth/restablecer':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->restablecerPassword();
        break;

    case 'reset-password':
        require BASE_PATH . '/app/views/auth/reset_password.php';
        break;

    //Aca son todas las rutas para el administrativo desde el modelo, vista y controlador

    //ESTAS SON LAS VISTAS (protegidas: requieren sesión con rol admin/rector)

    case 'inicio':
        // Migrado a Laravel (Fase 2): dashboard real con datos de la BD.
        header('Location: ' . LARAVEL_URL . 'inicio');
        break;

    case 'RegistroEstudiantes':
        // Migrado a Laravel (Fase 5): wizard real con backend completo.
        // Preserva el modo edición (?id=X) del enlace legacy.
        $idEdicion = (int) ($_GET['id'] ?? 0);
        $destino = $idEdicion > 0
            ? "registro/estudiantes/{$idEdicion}/editar"
            : 'registro/estudiantes';
        header('Location: ' . LARAVEL_URL . $destino);
        break;

    case 'RegistroDocentes':
        // Migrado a Laravel (Fase 5): antes era 100% maqueta sin backend.
        header('Location: ' . LARAVEL_URL . 'registro/docentes');
        break;
    case 'RegistroAdministrativos':
        // Migrado a Laravel (Fase 5): antes era 100% maqueta, el POST
        // original apuntaba a una ruta /administrativos/guardar inexistente.
        header('Location: ' . LARAVEL_URL . 'registro/administrativos');
        break;

    case 'Listados':
        // Migrado a Laravel (Fase 3): 3 pestañas con datos reales, búsqueda,
        // filtros y paginación server-side.
        header('Location: ' . LARAVEL_URL . 'listados');
        break;

    case 'Matriculas':
        // Migrado a Laravel (Fase 4): listado real con búsqueda, filtros,
        // paginación y cambio de estado real (la vista legacy era 100% maqueta).
        header('Location: ' . LARAVEL_URL . 'matriculas');
        break;

    case 'Estadisticas':
        Auth::requiereRol(Auth::ROLES_PANEL_ADMIN);
        require BASE_PATH . '/app/views/dashBoard/administracion/Estadisticas.php';
        break;

    case 'Reportes':
        Auth::requiereRol(Auth::ROLES_PANEL_ADMIN);
        require BASE_PATH . '/app/views/dashBoard/administracion/Reportes.php';
        break;

    case 'Comunicados':
        Auth::requiereRol(Auth::ROLES_PANEL_ADMIN);
        require BASE_PATH . '/app/views/dashBoard/administracion/Comunicados.php';
        break;

    case 'Observaciones':
        Auth::requiereRol(Auth::ROLES_PANEL_ADMIN);
        require BASE_PATH . '/app/views/dashBoard/administracion/Observaciones.php';
        break;

    case 'EditarLanding':
        Auth::requiereRol(Auth::ROLES_PANEL_ADMIN);
        require BASE_PATH . '/app/views/dashBoard/administracion/EditarLanding.php';
        break;

    case 'Perfil':
        Auth::requiereRol(Auth::ROLES_PANEL_ADMIN);
        require BASE_PATH . '/app/views/dashBoard/administracion/Perfil.php';
        break;
    //ESTAS SON LAS RUTAS PARA EL CONTROLADOR

    case 'api/estudiantes/crear':
        require_once BASE_PATH . '/app/controllers/EstudianteController.php';
        (new EstudianteController())->crear();
        break;

    case 'api/estudiantes/actualizar':
        require_once BASE_PATH . '/app/controllers/EstudianteController.php';
        (new EstudianteController())->actualizar();
        break;

    case 'api/estudiantes/eliminar':
        require_once BASE_PATH . '/app/controllers/EstudianteController.php';
        (new EstudianteController())->eliminar();
        break;

    case 'api/estudiantes/obtener':
        require_once BASE_PATH . '/app/controllers/EstudianteController.php';
        (new EstudianteController())->obtener();
        break;

    case 'api/estudiantes/listar':
        require_once BASE_PATH . '/app/controllers/EstudianteController.php';
        (new EstudianteController())->listar();
        break;

    default:
        // Si la ruta no coincide con ninguna de las anteriores, mostrar un error 404
        http_response_code(404);
        $base_path = BASE_PATH . '/app/views/auth/error404.php';
        if (file_exists($base_path)) {
            require $base_path;
        } else {
            echo "404 Not Found";
        }
        break;
    
}