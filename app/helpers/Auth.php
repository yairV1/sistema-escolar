<?php
/**
 * =====================================================
 * HELPER: Auth
 * =====================================================
 * Única fuente de verdad para sesión y control por rol.
 * No abre conexión a BD: solo lee/escribe $_SESSION.
 * El login real (verificación contra `usuarios`) vive en
 * AuthController, que llama a Auth::iniciarSesion().
 * =====================================================
 */

class Auth
{
    /** Mapea id_rol (tabla `roles`) a un slug corto usado en config/menu.php y en el login. */
    private const ROLE_SLUGS = [
        1 => 'admin',        // Administrador
        2 => 'rector',       // Directivo
        3 => 'coordinador',  // Coordinador
        4 => 'secretario',   // Secretario
        5 => 'docente',      // Profesor
        6 => 'estudiante',   // Estudiante
        7 => 'acudiente',    // Acudiente
    ];

    /** Roles con acceso al panel administrativo (dashBoard/administracion). */
    public const ROLES_PANEL_ADMIN = ['admin', 'rector'];

    public static function iniciarSesion(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['auth'] = [
            'id_usuario' => (int) $usuario['id_usuario'],
            'nombres'    => $usuario['nombres'],
            'apellidos'  => $usuario['apellidos'],
            'correo'     => $usuario['correo'],
            'id_rol'     => (int) $usuario['id_rol'],
            'rol'        => self::ROLE_SLUGS[(int) $usuario['id_rol']] ?? 'desconocido',
        ];
    }

    public static function cerrarSesion(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function estaAutenticado(): bool
    {
        return isset($_SESSION['auth']['id_usuario']);
    }

    public static function usuario(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['auth']['id_usuario'] ?? null;
    }

    public static function rol(): ?string
    {
        return $_SESSION['auth']['rol'] ?? null;
    }

    /** @return string[] Roles del usuario actual (hoy siempre uno, pero se expone como array por si se agregan roles múltiples). */
    public static function roles(): array
    {
        $rol = self::rol();
        return $rol ? [$rol] : [];
    }

    /** Exige sesión activa en vistas HTML; si no hay sesión, redirige a login. */
    public static function requiereLogin(): void
    {
        if (!self::estaAutenticado()) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /** Exige sesión + uno de los roles permitidos en vistas HTML; si no cumple, muestra 403. */
    public static function requiereRol(array $rolesPermitidos): void
    {
        self::requiereLogin();
        if (!in_array(self::rol(), $rolesPermitidos, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/views/auth/acceso_denegado.php';
            exit;
        }
    }

    /** Igual que requiereRol() pero para endpoints de API: responde JSON en vez de redirigir/renderizar HTML. */
    public static function requiereRolApi(array $rolesPermitidos): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!self::estaAutenticado()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (!in_array(self::rol(), $rolesPermitidos, true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
