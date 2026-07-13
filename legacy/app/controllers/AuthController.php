<?php
/**
 * =====================================================
 * CONTROLLER: AuthController
 * =====================================================
 * Punto de entrada HTTP para autenticación.
 * login() es consumido por fetch() desde login.js y
 * responde en JSON, igual que EstudianteController.
 * =====================================================
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Mailer.php';

class AuthController
{
    private Usuario $model;
    private PasswordReset $resets;

    public function __construct()
    {
        $this->model = new Usuario();
        $this->resets = new PasswordReset($this->model->pdo());
    }

    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function login(): void
    {
        $identificador = trim($_POST['usuario'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($identificador === '' || $password === '') {
            $this->json(['success' => false, 'message' => 'Usuario y contraseña son obligatorios.'], 422);
        }

        try {
            $usuario = filter_var($identificador, FILTER_VALIDATE_EMAIL)
                ? $this->model->buscarPorCorreo($identificador)
                : $this->model->buscarPorDocumento($identificador);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => 'No se pudo verificar tus credenciales. Intenta más tarde.'], 500);
        }

        // Mensaje genérico e idéntico si el usuario no existe o el password no coincide:
        // no revelar cuál de los dos falló (evita enumeración de cuentas).
        if (!$usuario || !password_verify($password, $usuario['password'])) {
            $this->json(['success' => false, 'message' => 'Usuario o contraseña incorrectos.'], 401);
        }

        if ($usuario['estado_usuario'] !== 'activo') {
            $this->json(['success' => false, 'message' => 'Tu cuenta se encuentra inactiva o bloqueada. Contacta al colegio.'], 403);
        }

        Auth::iniciarSesion($usuario);
        $this->model->registrarAcceso((int) $usuario['id_usuario']);

        $redirect = in_array(Auth::rol(), Auth::ROLES_PANEL_ADMIN, true)
            ? BASE_URL . 'inicio'
            : BASE_URL;

        $this->json(['success' => true, 'redirect' => $redirect]);
    }

    public function logout(): void
    {
        Auth::cerrarSesion();
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    public function solicitarRecuperacion(): void
    {
        $correo = trim($_POST['correo'] ?? '');
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Ingresa un correo válido.'], 422);
        }

        $usuario = $this->model->buscarPorCorreo($correo);

        // Mismo mensaje de éxito exista o no la cuenta: evita revelar qué correos están registrados.
        if ($usuario && $usuario['estado_usuario'] === 'activo') {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $idUsuario = (int) $usuario['id_usuario'];

            $this->resets->invalidarPendientes($idUsuario);
            $this->resets->crear($idUsuario, $tokenHash, date('Y-m-d H:i:s', time() + 1800));

            $enlace = BASE_URL . 'reset-password?token=' . $token;
            $nombreCompleto = trim($usuario['nombres'] . ' ' . $usuario['apellidos']);
            Mailer::enviarRecuperacionPassword($usuario['correo'], $nombreCompleto, $enlace);
        }

        $this->json([
            'success' => true,
            'message' => 'Si el correo existe en nuestro sistema, te enviamos un enlace para restablecer tu contraseña.',
        ]);
    }

    public function restablecerPassword(): void
    {
        $token = trim($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($token === '' || strlen($password) < 6) {
            $this->json(['success' => false, 'message' => 'Datos inválidos. La contraseña debe tener mínimo 6 caracteres.'], 422);
        }

        $tokenHash = hash('sha256', $token);
        $reset = $this->resets->buscarValidoPorHash($tokenHash);
        if (!$reset) {
            $this->json(['success' => false, 'message' => 'El enlace no es válido o ya expiró. Solicita uno nuevo.'], 400);
        }

        $this->model->actualizarPassword((int) $reset['id_usuario'], $password);
        $this->resets->marcarUsado((int) $reset['id_reset']);

        $this->json(['success' => true, 'message' => 'Tu contraseña fue actualizada. Ya puedes iniciar sesión.']);
    }
}
