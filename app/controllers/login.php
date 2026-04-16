<?php

require_once __DIR__ . '/../../helpers/alert_helper.php';
require_once __DIR__ . '/../model/login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    // 🔴 Validación básica
    if (empty($correo) || empty($password)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos');
        exit();
    }

    $login = new Login();
    $resultado = $login->autenticar($correo, $password);

    // 🔴 Error de autenticación
    if (isset($resultado['error'])) {
        mostrarSweetAlert('error', 'Error de autenticación', $resultado['error']);
        exit();
    }

    // ✅ Crear sesión
    session_start();
    $_SESSION['user'] = [
        'id_usuario' => $resultado['id_usuario'],
        'nombres' => $resultado['nombres'],
        'rol' => $resultado['nombre_rol'] // guardamos el nombre del rol
    ];

    // 🔵 Redirección según rol
    $redirectUrl = '/colegio/login';
    $mensaje = 'Rol inexistente. Redirigiendo...';

    switch (strtolower(trim($resultado['nombre_rol']))) {

        case 'administrador': 
            $redirectUrl = '/colegio/administrador/Inicio'; 
            $mensaje = 'Bienvenido Administrador.'; 
            break; 
        case 'directivo': 
            $redirectUrl = '/colegio/directivo/Inicio'; 
            $mensaje = 'Bienvenido Directivo.'; 
            break; 
        case 1: 
            $redirectUrl = '/colegio/profesor/dashboard'; 
            $mensaje = 'Bienvenido Directivo.'; 
            break; 
        case 'coordinador': 
            $redirectUrl = '/colegio/estudiante/dashboard'; 
            $mensaje = 'Bienvenido Coordinador.'; 
            break; 
    }

    // ✅ Mostrar alerta y redirigir
    mostrarSweetAlert('success', 'Ingreso exitoso', $mensaje, $redirectUrl);
    exit();

} else {
    http_response_code(405);
    echo "Metodo no permitido";
    exit();
}