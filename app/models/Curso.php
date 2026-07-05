<?php
/**
 * =====================================================
 * MODEL: Curso
 * =====================================================
 * Un curso es la combinación grado+grupo para un año
 * lectivo y jornada concretos (ej: "11A", 2025, mañana).
 * =====================================================
 */

require_once __DIR__ . '/../../config/database.php';

class Curso
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? (new Conexion())->getConexion();
    }

    private const NIVEL_POR_GRADO = [
        'PRE' => 'preescolar',
        '1' => 'primaria', '2' => 'primaria', '3' => 'primaria', '4' => 'primaria', '5' => 'primaria',
        '6' => 'secundaria', '7' => 'secundaria', '8' => 'secundaria', '9' => 'secundaria',
        '10' => 'media', '11' => 'media',
    ];

    public static function nivelAcademicoDeGrado(string $grado): string
    {
        return self::NIVEL_POR_GRADO[$grado] ?? 'primaria';
    }

    /**
     * Busca el curso para ese nombre/año/jornada; si no existe, lo crea.
     * Devuelve el id_curso.
     */
    public function buscarOCrear(string $nombreCurso, string $nivelAcademico, string $jornada, string $anioLectivo): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_curso FROM cursos WHERE nombre_curso = :nombre AND anio_lectivo = :anio LIMIT 1'
        );
        $stmt->execute(['nombre' => $nombreCurso, 'anio' => $anioLectivo]);
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existente) {
            return (int) $existente['id_curso'];
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO cursos (nombre_curso, nivel_academico, jornada, anio_lectivo, estado)
             VALUES (:nombre, :nivel, :jornada, :anio, "activo")'
        );
        $stmt->execute([
            'nombre' => $nombreCurso,
            'nivel'  => $nivelAcademico,
            'jornada'=> $jornada,
            'anio'   => $anioLectivo,
        ]);
        return (int) $this->pdo->lastInsertId();
    }
}
