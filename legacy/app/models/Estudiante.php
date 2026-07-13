<?php
/**
 * =====================================================
 * MODEL: Estudiante
 * =====================================================
 * Orquesta el registro completo de un estudiante:
 * usuario base + datos académicos + curso/matrícula +
 * acudiente. Las 4 tablas se escriben en una sola
 * transacción para no dejar datos huérfanos.
 * =====================================================
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Curso.php';
require_once __DIR__ . '/Acudiente.php';

class Estudiante
{
    private PDO $pdo;
    private Usuario $usuarioModel;
    private Curso $cursoModel;
    private Acudiente $acudienteModel;

    public const ID_ROL_ESTUDIANTE = 6;

    private const PARENTESCO_MAP = [
        'madre' => 'madre',
        'padre' => 'padre',
        'abuelo' => 'abuelo',
        'tio' => 'tio',
        'hermano' => 'hermano',
        'acudiente' => 'tutor_legal',
        'otro' => 'otro',
    ];

    public function __construct()
    {
        $this->pdo = (new Conexion())->getConexion();
        // Se inyecta la MISMA conexión en los sub-modelos para que
        // beginTransaction()/commit()/rollBack() cubran todos los inserts.
        $this->usuarioModel = new Usuario($this->pdo);
        $this->cursoModel = new Curso($this->pdo);
        $this->acudienteModel = new Acudiente($this->pdo);
    }

    /**
     * Valida los campos obligatorios y formatos. Devuelve un array
     * [campo => mensaje] vacío cuando todo es válido.
     */
    public function validar(array $d, bool $esEdicion = false): array
    {
        $errores = [];
        $req = function (string $campo, string $msg) use (&$errores, $d) {
            if (trim((string) ($d[$campo] ?? '')) === '') {
                $errores[$campo] = $msg;
            }
        };

        $req('primerNombre', 'El primer nombre es obligatorio.');
        $req('primerApellido', 'El primer apellido es obligatorio.');
        $req('tipoDoc', 'Selecciona el tipo de documento.');
        $req('numDoc', 'El número de documento es obligatorio.');
        $req('fechaNac', 'La fecha de nacimiento es obligatoria.');
        $req('genero', 'Selecciona el género.');
        $req('direccion', 'La dirección es obligatoria.');
        $req('localidad', 'Selecciona la localidad.');
        $req('ciudad', 'La ciudad es obligatoria.');
        $req('tipoMatricula', 'Selecciona el tipo de matrícula.');
        $req('anioLectivo', 'Selecciona el año lectivo.');
        $req('grado', 'Selecciona el grado.');
        $req('jornada', 'Selecciona la jornada.');

        if (!$esEdicion || !empty($d['acuNombres'])) {
            $req('acuNombres', 'El nombre del acudiente es obligatorio.');
            $req('acuParentesco', 'Selecciona el parentesco.');
            $req('acuTipoDoc', 'Selecciona el tipo de documento del acudiente.');
            $req('acuNumDoc', 'El número de documento del acudiente es obligatorio.');
            $req('acuTel', 'El teléfono del acudiente es obligatorio.');
            $req('acuEmail', 'El correo del acudiente es obligatorio.');
        }

        if (!empty($d['fechaNac']) && !$this->esFechaValida($d['fechaNac'])) {
            $errores['fechaNac'] = 'La fecha de nacimiento no es válida.';
        }
        if (!empty($d['emailEstudiante']) && !filter_var($d['emailEstudiante'], FILTER_VALIDATE_EMAIL)) {
            $errores['emailEstudiante'] = 'Ingresa un correo válido.';
        }
        if (!empty($d['acuEmail']) && !filter_var($d['acuEmail'], FILTER_VALIDATE_EMAIL)) {
            $errores['acuEmail'] = 'Ingresa un correo válido para el acudiente.';
        }
        if (!empty($d['telEstudiante']) && !preg_match('/^[0-9+\s-]{7,20}$/', $d['telEstudiante'])) {
            $errores['telEstudiante'] = 'El teléfono no tiene un formato válido.';
        }
        if (!empty($d['acuTel']) && !preg_match('/^[0-9+\s-]{7,20}$/', $d['acuTel'])) {
            $errores['acuTel'] = 'El teléfono no tiene un formato válido.';
        }

        return $errores;
    }

    private function esFechaValida(string $fecha): bool
    {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        return $dt !== false && $dt->format('Y-m-d') === $fecha;
    }

    /**
     * Registra un estudiante completo (usuario, estudiante, curso/matrícula,
     * acudiente). Devuelve ['id_estudiante' => int, 'codigo_estudiante' => string].
     */
    public function crear(array $d): array
    {
        $this->pdo->beginTransaction();
        try {
            $nombres = trim($d['primerNombre'] . ' ' . ($d['segundoNombre'] ?? ''));
            $apellidos = trim($d['primerApellido'] . ' ' . ($d['segundoApellido'] ?? ''));
            $correo = !empty($d['emailEstudiante'])
                ? $d['emailEstudiante']
                : $d['numDoc'] . '@estudiantes.sancristobal.edu.co';

            $idUsuario = $this->usuarioModel->crear([
                'nombres'          => $nombres,
                'apellidos'        => $apellidos,
                'tipo_documento'   => $d['tipoDoc'],
                'numero_documento' => $d['numDoc'],
                'correo'           => $correo,
                'telefono'         => $d['telEstudiante'] ?? null,
                'password'         => $d['numDoc'],
                'id_rol'           => self::ID_ROL_ESTUDIANTE,
            ]);

            $codigoEstudiante = $this->generarCodigoUnico();

            $stmt = $this->pdo->prepare(
                'INSERT INTO estudiantes
                    (id_usuario, codigo_estudiante, fecha_nacimiento, genero, direccion, eps_seguro, estado_academico, fecha_ingreso, observaciones_gral)
                 VALUES
                    (:id_usuario, :codigo, :fecha_nac, :genero, :direccion, :eps, "activo", CURDATE(), :obs)'
            );
            $stmt->execute([
                'id_usuario' => $idUsuario,
                'codigo'     => $codigoEstudiante,
                'fecha_nac'  => $d['fechaNac'],
                'genero'     => $this->mapGenero($d['genero']),
                'direccion'  => $d['direccion'] . ($d['barrio'] ?? '' ? ', ' . $d['barrio'] : '') . ', ' . $d['localidad'] . ', ' . $d['ciudad'],
                'eps'        => $d['eps'] ?? null,
                'obs'        => $d['condicion'] ?? null,
            ]);
            $idEstudiante = (int) $this->pdo->lastInsertId();

            $this->asignarCursoYMatricula($idEstudiante, $d);

            $idAcudiente = $this->acudienteModel->buscarOCrear([
                'nombres'          => $this->primeraPalabra($d['acuNombres'], true),
                'apellidos'        => $this->primeraPalabra($d['acuNombres'], false),
                'tipo_documento'   => $d['acuTipoDoc'],
                'numero_documento' => $d['acuNumDoc'],
                'correo'           => $d['acuEmail'],
                'telefono'         => $d['acuTel'],
                'ocupacion'        => $d['acuOcupacion'] ?? null,
            ]);
            $this->acudienteModel->vincularEstudiante(
                $idEstudiante,
                $idAcudiente,
                self::PARENTESCO_MAP[$d['acuParentesco']] ?? 'otro',
                true
            );

            $this->pdo->commit();
            return ['id_estudiante' => $idEstudiante, 'codigo_estudiante' => $codigoEstudiante];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function asignarCursoYMatricula(int $idEstudiante, array $d): void
    {
        $grupo = $d['grupo'] ?? '';
        $nombreCurso = $d['grado'] . ($grupo !== '' ? $grupo : 'A');
        $idCurso = $this->cursoModel->buscarOCrear(
            $nombreCurso,
            Curso::nivelAcademicoDeGrado($d['grado']),
            $d['jornada'],
            $d['anioLectivo']
        );

        $stmt = $this->pdo->prepare(
            'INSERT INTO matriculas (id_estudiante, id_curso, anio_lectivo, fecha_matricula, estado_matricula, observacion)
             VALUES (:est, :curso, :anio, CURDATE(), "activa", :obs)
             ON DUPLICATE KEY UPDATE id_curso = VALUES(id_curso), observacion = VALUES(observacion)'
        );
        $stmt->execute([
            'est'   => $idEstudiante,
            'curso' => $idCurso,
            'anio'  => $d['anioLectivo'],
            'obs'   => $d['tipoMatricula'] ?? null,
        ]);
    }

    private function mapGenero(string $genero): string
    {
        return match ($genero) {
            'M' => 'M',
            'F' => 'F',
            default => 'Otro',
        };
    }

    private function primeraPalabra(string $nombreCompleto, bool $inicio): string
    {
        $partes = preg_split('/\s+/', trim($nombreCompleto));
        if (count($partes) === 1) {
            return $inicio ? $partes[0] : $partes[0];
        }
        $ultimo = array_pop($partes);
        return $inicio ? implode(' ', $partes) : $ultimo;
    }

    private function generarCodigoUnico(): string
    {
        $anio = date('Y');
        do {
            $stmt = $this->pdo->query('SELECT COUNT(*) AS c FROM estudiantes');
            $siguiente = (int) $stmt->fetch(PDO::FETCH_ASSOC)['c'] + 1;
            $codigo = sprintf('%s-EST-%04d', $anio, $siguiente);
            $check = $this->pdo->prepare('SELECT 1 FROM estudiantes WHERE codigo_estudiante = :c');
            $check->execute(['c' => $codigo]);
            $existe = (bool) $check->fetchColumn();
        } while ($existe);
        return $codigo;
    }

    /**
     * Listado con datos reales para la tabla de Listados.php.
     */
    public function listar(): array
    {
        $sql = 'SELECT
                    e.id_estudiante, e.codigo_estudiante, e.estado_academico,
                    u.nombres, u.apellidos, u.correo,
                    c.nombre_curso, c.jornada, m.anio_lectivo
                FROM estudiantes e
                JOIN usuarios u ON u.id_usuario = e.id_usuario
                LEFT JOIN matriculas m ON m.id_estudiante = e.id_estudiante
                    AND m.anio_lectivo = (
                        SELECT MAX(m2.anio_lectivo) FROM matriculas m2 WHERE m2.id_estudiante = e.id_estudiante
                    )
                LEFT JOIN cursos c ON c.id_curso = m.id_curso
                ORDER BY u.apellidos, u.nombres';
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $idEstudiante): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, u.*, m.anio_lectivo, m.observacion AS tipo_matricula, c.nombre_curso, c.jornada
             FROM estudiantes e
             JOIN usuarios u ON u.id_usuario = e.id_usuario
             LEFT JOIN matriculas m ON m.id_estudiante = e.id_estudiante
                 AND m.anio_lectivo = (SELECT MAX(m2.anio_lectivo) FROM matriculas m2 WHERE m2.id_estudiante = e.id_estudiante)
             LEFT JOIN cursos c ON c.id_curso = m.id_curso
             WHERE e.id_estudiante = :id LIMIT 1'
        );
        $stmt->execute(['id' => $idEstudiante]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $acuStmt = $this->pdo->prepare(
            'SELECT u.*, ea.parentesco, ac.ocupacion
             FROM estudiante_acudiente ea
             JOIN acudientes ac ON ac.id_acudiente = ea.id_acudiente
             JOIN usuarios u ON u.id_usuario = ac.id_usuario
             WHERE ea.id_estudiante = :id AND ea.es_principal = 1 LIMIT 1'
        );
        $acuStmt->execute(['id' => $idEstudiante]);
        $row['acudiente'] = $acuStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        // El hash de contraseña nunca debe salir de la capa de datos.
        unset($row['password']);
        if ($row['acudiente']) {
            unset($row['acudiente']['password']);
        }

        return $row;
    }

    public function actualizar(int $idEstudiante, array $d): void
    {
        $actual = $this->obtenerPorId($idEstudiante);
        if (!$actual) {
            throw new RuntimeException('Estudiante no encontrado.');
        }

        $this->pdo->beginTransaction();
        try {
            $nombres = trim($d['primerNombre'] . ' ' . ($d['segundoNombre'] ?? ''));
            $apellidos = trim($d['primerApellido'] . ' ' . ($d['segundoApellido'] ?? ''));
            $correo = !empty($d['emailEstudiante']) ? $d['emailEstudiante'] : $actual['correo'];

            $this->usuarioModel->actualizar((int) $actual['id_usuario'], [
                'nombres'          => $nombres,
                'apellidos'        => $apellidos,
                'tipo_documento'   => $d['tipoDoc'],
                'numero_documento' => $d['numDoc'],
                'correo'           => $correo,
                'telefono'         => $d['telEstudiante'] ?? null,
            ]);

            $stmt = $this->pdo->prepare(
                'UPDATE estudiantes SET
                    fecha_nacimiento = :fecha_nac,
                    genero = :genero,
                    direccion = :direccion,
                    eps_seguro = :eps,
                    observaciones_gral = :obs
                 WHERE id_estudiante = :id'
            );
            $stmt->execute([
                'fecha_nac' => $d['fechaNac'],
                'genero'    => $this->mapGenero($d['genero']),
                'direccion' => $d['direccion'] . ($d['barrio'] ?? '' ? ', ' . $d['barrio'] : '') . ', ' . $d['localidad'] . ', ' . $d['ciudad'],
                'eps'       => $d['eps'] ?? null,
                'obs'       => $d['condicion'] ?? null,
                'id'        => $idEstudiante,
            ]);

            $this->asignarCursoYMatricula($idEstudiante, $d);

            if (!empty($d['acuNombres']) && !empty($d['acuNumDoc'])) {
                $idAcudiente = $this->acudienteModel->buscarOCrear([
                    'nombres'          => $this->primeraPalabra($d['acuNombres'], true),
                    'apellidos'        => $this->primeraPalabra($d['acuNombres'], false),
                    'tipo_documento'   => $d['acuTipoDoc'],
                    'numero_documento' => $d['acuNumDoc'],
                    'correo'           => $d['acuEmail'],
                    'telefono'         => $d['acuTel'],
                    'ocupacion'        => $d['acuOcupacion'] ?? null,
                ]);
                $this->acudienteModel->vincularEstudiante(
                    $idEstudiante,
                    $idAcudiente,
                    self::PARENTESCO_MAP[$d['acuParentesco']] ?? 'otro',
                    true
                );
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Baja lógica: nunca se borra un registro académico, se inactiva. */
    public function eliminar(int $idEstudiante): void
    {
        $actual = $this->obtenerPorId($idEstudiante);
        if (!$actual) {
            throw new RuntimeException('Estudiante no encontrado.');
        }
        $stmt = $this->pdo->prepare('UPDATE estudiantes SET estado_academico = "inactivo" WHERE id_estudiante = :id');
        $stmt->execute(['id' => $idEstudiante]);
        $this->usuarioModel->cambiarEstado((int) $actual['id_usuario'], 'inactivo');
    }
}
