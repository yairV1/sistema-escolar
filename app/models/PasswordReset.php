<?php
/**
 * =====================================================
 * MODEL: PasswordReset
 * =====================================================
 * Tokens de un solo uso para recuperación de contraseña.
 * Solo se persiste el hash SHA-256 del token; el token en
 * texto plano únicamente viaja en el enlace del correo.
 * =====================================================
 */

require_once __DIR__ . '/../../config/database.php';

class PasswordReset
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? (new Conexion())->getConexion();
    }

    public function crear(int $idUsuario, string $tokenHash, string $expiraEn): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO password_resets (id_usuario, token_hash, expira_en) VALUES (:id, :hash, :exp)'
        );
        $stmt->execute(['id' => $idUsuario, 'hash' => $tokenHash, 'exp' => $expiraEn]);
    }

    public function buscarValidoPorHash(string $tokenHash): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM password_resets WHERE token_hash = :hash AND usado = 0 AND expira_en > NOW() LIMIT 1'
        );
        $stmt->execute(['hash' => $tokenHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function marcarUsado(int $idReset): void
    {
        $stmt = $this->pdo->prepare('UPDATE password_resets SET usado = 1 WHERE id_reset = :id');
        $stmt->execute(['id' => $idReset]);
    }

    /** Invalida cualquier token previo sin usar del usuario, para que solo el más reciente sirva. */
    public function invalidarPendientes(int $idUsuario): void
    {
        $stmt = $this->pdo->prepare('UPDATE password_resets SET usado = 1 WHERE id_usuario = :id AND usado = 0');
        $stmt->execute(['id' => $idUsuario]);
    }
}
