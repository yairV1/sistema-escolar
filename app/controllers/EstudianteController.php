<?php
/**
 * =====================================================
 * CONTROLLER: EstudianteController
 * =====================================================
 * Punto de entrada HTTP para el CRUD de estudiantes.
 * Todas las acciones responden en JSON (consumidas por
 * fetch() desde registro_estudiante.js y listado.js).
 * =====================================================
 */

require_once __DIR__ . '/../models/Estudiante.php';

class EstudianteController
{
    private Estudiante $model;

    public function __construct()
    {
        $this->model = new Estudiante();
    }

    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function crear(): void
    {
        $d = $_POST;
        $errores = $this->model->validar($d, false);
        if ($errores) {
            $this->json(['success' => false, 'errors' => $errores], 422);
        }

        try {
            $resultado = $this->model->crear($d);
            $this->json(['success' => true, 'data' => $resultado]);
        } catch (PDOException $e) {
            $this->manejarErrorDuplicado($e);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => 'No se pudo guardar el registro.'], 500);
        }
    }

    public function actualizar(): void
    {
        $id = (int) ($_POST['id_estudiante'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Falta el identificador del estudiante.'], 400);
        }

        $errores = $this->model->validar($_POST, true);
        if ($errores) {
            $this->json(['success' => false, 'errors' => $errores], 422);
        }

        try {
            $this->model->actualizar($id, $_POST);
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->manejarErrorDuplicado($e);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function eliminar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Falta el identificador del estudiante.'], 400);
        }
        try {
            $this->model->eliminar($id);
            $this->json(['success' => true]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function obtener(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Falta el identificador del estudiante.'], 400);
        }
        $data = $this->model->obtenerPorId($id);
        if (!$data) {
            $this->json(['success' => false, 'message' => 'Estudiante no encontrado.'], 404);
        }
        $this->json(['success' => true, 'data' => $data]);
    }

    public function listar(): void
    {
        $this->json(['success' => true, 'data' => $this->model->listar()]);
    }

    private function manejarErrorDuplicado(PDOException $e): void
    {
        // 1062 = entrada duplicada en una clave UNIQUE (correo o documento)
        if ((int) $e->errorInfo[1] === 1062) {
            $this->json(['success' => false, 'message' => 'Ya existe un usuario con ese documento o correo.'], 409);
        }
        $this->json(['success' => false, 'message' => 'No se pudo guardar el registro.'], 500);
    }
}
