<?php
/**
 * =====================================================
 * MODEL: Acudiente
 * =====================================================
 * Padre/madre o responsable legal de uno o varios
 * estudiantes. Extiende `usuarios` (rol Acudiente).
 * =====================================================
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Usuario.php';

class Acudiente
{
    private PDO $pdo;
    private Usuario $usuarioModel;

    public const ID_ROL_ACUDIENTE = 7;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? (new Conexion())->getConexion();
        $this->usuarioModel = new Usuario($this->pdo);
    }

    /**
     * Busca un acudiente existente por documento; si no existe,
     * crea su usuario base + registro de acudiente. Devuelve id_acudiente.
     */
    public function buscarOCrear(array $datos): int
    {
        $usuarioExistente = $this->usuarioModel->buscarPorDocumento($datos['numero_documento']);

        if ($usuarioExistente) {
            $stmt = $this->pdo->prepare('SELECT id_acudiente FROM acudientes WHERE id_usuario = :id LIMIT 1');
            $stmt->execute(['id' => $usuarioExistente['id_usuario']]);
            $acudiente = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($acudiente) {
                return (int) $acudiente['id_acudiente'];
            }
            $idUsuario = (int) $usuarioExistente['id_usuario'];
        } else {
            $idUsuario = $this->usuarioModel->crear([
                'nombres'          => $datos['nombres'],
                'apellidos'        => $datos['apellidos'],
                'tipo_documento'   => $datos['tipo_documento'],
                'numero_documento' => $datos['numero_documento'],
                'correo'           => $datos['correo'],
                'telefono'         => $datos['telefono'] ?? null,
                'password'         => $datos['numero_documento'],
                'id_rol'           => self::ID_ROL_ACUDIENTE,
            ]);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO acudientes (id_usuario, ocupacion, estado) VALUES (:id_usuario, :ocupacion, "activo")'
        );
        $stmt->execute([
            'id_usuario' => $idUsuario,
            'ocupacion'  => $datos['ocupacion'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function vincularEstudiante(int $idEstudiante, int $idAcudiente, string $parentesco, bool $esPrincipal): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_rel FROM estudiante_acudiente WHERE id_estudiante = :est AND id_acudiente = :acu LIMIT 1'
        );
        $stmt->execute(['est' => $idEstudiante, 'acu' => $idAcudiente]);
        if ($stmt->fetch()) {
            return; // ya vinculado
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO estudiante_acudiente (id_estudiante, id_acudiente, parentesco, es_principal, estado)
             VALUES (:est, :acu, :parentesco, :principal, "activo")'
        );
        $stmt->execute([
            'est'        => $idEstudiante,
            'acu'        => $idAcudiente,
            'parentesco' => $parentesco,
            'principal'  => $esPrincipal ? 1 : 0,
        ]);
    }
}
