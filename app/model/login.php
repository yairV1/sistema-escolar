<?php

require_once __DIR__ . '/../../config/database.php';

class Login
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    public function autenticar($correo, $password)
    {
        $sql = "SELECT 
                    u.id_usuario,
                    u.nombres,
                    u.correo,
                    u.password,
                    u.id_rol,
                    r.nombre_rol
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.correo = :correo 
                AND u.estado_usuario = 'activo'
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // ❌ Usuario no existe
        if (!$usuario) {
            return ['error' => 'El usuario no existe'];
        }

        // ❌ Contraseña incorrecta
        if (!password_verify($password, $usuario['password'])) {
            return ['error' => 'Contraseña incorrecta'];
        }

        // ✅ Login correcto
        return $usuario;
    }
}