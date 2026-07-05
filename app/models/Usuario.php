<?php
/**
 * =====================================================
 * MODEL: Usuario
 * =====================================================
 * Tabla central de autenticación y datos personales.
 * Todas las entidades (estudiante, profesor, acudiente,
 * administrativo) tienen un registro base aquí.
 * =====================================================
 */

require_once __DIR__ . '/../../config/database.php';

class Usuario
{
    private PDO $pdo;

    /** Si no se inyecta una conexión, abre la suya propia (uso standalone). */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? (new Conexion())->getConexion();
    }

    public function buscarPorDocumento(string $numeroDocumento): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE numero_documento = :doc LIMIT 1');
        $stmt->execute(['doc' => $numeroDocumento]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function buscarPorCorreo(string $correo): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE correo = :correo LIMIT 1');
        $stmt->execute(['correo' => $correo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function buscarPorId(int $idUsuario): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute(['id' => $idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crea el registro base en `usuarios` y devuelve el id_usuario generado.
     * $datos: nombres, apellidos, tipo_documento, numero_documento, correo,
     *         telefono, id_rol, password (texto plano, se hashea aquí).
     */
    public function crear(array $datos): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios
                (nombres, apellidos, tipo_documento, numero_documento, correo, telefono, password, id_rol, estado_usuario)
             VALUES
                (:nombres, :apellidos, :tipo_documento, :numero_documento, :correo, :telefono, :password, :id_rol, "activo")'
        );
        $stmt->execute([
            'nombres'          => $datos['nombres'],
            'apellidos'        => $datos['apellidos'],
            'tipo_documento'   => $datos['tipo_documento'],
            'numero_documento' => $datos['numero_documento'],
            'correo'           => $datos['correo'],
            'telefono'         => $datos['telefono'] ?? null,
            'password'         => password_hash($datos['password'], PASSWORD_BCRYPT),
            'id_rol'           => $datos['id_rol'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizar(int $idUsuario, array $datos): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET
                nombres = :nombres,
                apellidos = :apellidos,
                tipo_documento = :tipo_documento,
                numero_documento = :numero_documento,
                correo = :correo,
                telefono = :telefono
             WHERE id_usuario = :id'
        );
        $stmt->execute([
            'nombres'          => $datos['nombres'],
            'apellidos'        => $datos['apellidos'],
            'tipo_documento'   => $datos['tipo_documento'],
            'numero_documento' => $datos['numero_documento'],
            'correo'           => $datos['correo'],
            'telefono'         => $datos['telefono'] ?? null,
            'id'               => $idUsuario,
        ]);
    }

    public function cambiarEstado(int $idUsuario, string $estado): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET estado_usuario = :estado WHERE id_usuario = :id');
        $stmt->execute(['estado' => $estado, 'id' => $idUsuario]);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
