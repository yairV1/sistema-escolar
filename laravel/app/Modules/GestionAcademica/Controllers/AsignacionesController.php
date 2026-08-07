<?php

namespace App\Modules\GestionAcademica\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\GestionAcademica\Models\Materia;
use App\Modules\GestionAcademica\Requests\AsignacionRequest;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AsignacionesController extends Controller
{
    public function index(Request $request): View
    {
        return view('Rector.gestion-academica.asignaciones.index', [
            'currentPage' => 'GestionAcademicaAsignaciones',
            'filtros' => $request->only(['curso', 'anio', 'estado']),
            'profesores' => Profesor::with('usuario')->where('estado_laboral', 'activo')->get(),
            'todasLasMaterias' => Materia::where('estado', 'activo')->orderBy('nombre_materia')->get(),
            'todosLosCursos' => Curso::where('estado', 'activo')->orderBy('nombre_curso')->get(),
            'asignaciones' => $this->buscarAsignaciones($request),
            'resumenAsignaciones' => $this->resumenAsignaciones(),
        ]);
    }

    private function buscarAsignaciones(Request $request)
    {
        $query = AsignacionAcademica::query()->with(['profesor.usuario', 'materia', 'curso']);

        if ($curso = $request->query('curso')) {
            $query->where('id_curso', $curso);
        }

        if ($anio = $request->query('anio')) {
            $query->where('anio_lectivo', $anio);
        }

        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('estado', $estado);
        }

        return $query->orderByDesc('anio_lectivo')->paginate(15)->withQueryString();
    }

    private function resumenAsignaciones(): array
    {
        return [
            'total' => AsignacionAcademica::count(),
            'activas' => AsignacionAcademica::where('estado', 'activo')->count(),
            'inactivas' => AsignacionAcademica::where('estado', 'inactivo')->count(),
        ];
    }

    public function store(AsignacionRequest $request): JsonResponse
    {
        try {
            $asignacion = AsignacionAcademica::create($request->validated() + ['estado' => 'activo']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ese profesor ya tiene asignada esa materia en ese curso y año.');
        }

        return response()->json(['success' => true, 'message' => 'Asignación creada correctamente.', 'id' => $asignacion->id_asignacion]);
    }

    public function desactivar(AsignacionAcademica $asignacion): JsonResponse
    {
        $asignacion->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Asignación desactivada correctamente.']);
    }

    public function activar(AsignacionAcademica $asignacion): JsonResponse
    {
        $asignacion->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Asignación reactivada correctamente.']);
    }

    private function respuestaDuplicado(QueryException $e, string $mensaje): JsonResponse
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json(['success' => false, 'message' => $mensaje], 409);
        }

        throw $e;
    }
}
