<?php

namespace App\Modules\GestionAcademica\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\GestionAcademica\Models\Horario;
use App\Modules\GestionAcademica\Requests\CursoRequest;
use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CursosController extends Controller
{
    public function index(Request $request): View
    {
        return view('Rector.gestion-academica.cursos.index', [
            'currentPage' => 'GestionAcademicaCursos',
            'filtros' => $request->only(['q', 'estado', 'nivel']),
            'profesores' => Profesor::with('usuario')->where('estado_laboral', 'activo')->get(),
            'cursos' => $this->buscarCursos($request),
            'resumenCursos' => $this->resumenCursos(),
        ]);
    }

    public function show(Curso $curso): View
    {
        $curso->load('director.usuario');

        $estudiantes = $curso->matriculas()
            ->where('estado_matricula', 'activa')
            ->with('estudiante.usuario')
            ->get()
            ->pluck('estudiante');

        $asignaciones = $curso->asignaciones()
            ->where('estado', 'activo')
            ->with(['materia', 'profesor.usuario'])
            ->get();

        $horarios = Horario::whereHas('asignacion', fn ($q) => $q->where('id_curso', $curso->id_curso))
            ->where('estado', 'activo')
            ->with(['asignacion.materia', 'asignacion.profesor.usuario'])
            ->orderByRaw("FIELD(dia_semana,'lunes','martes','miercoles','jueves','viernes','sabado')")
            ->orderBy('hora_inicio')
            ->get();

        return view('Rector.gestion-academica.cursos.show', [
            'currentPage' => 'GestionAcademicaCursos',
            'curso' => $curso,
            'estudiantes' => $estudiantes,
            'asignaciones' => $asignaciones,
            'horarios' => $horarios,
            'idsEnRiesgo' => Estudiante::idsEnRiesgo(),
        ]);
    }

    private function buscarCursos(Request $request)
    {
        $query = Curso::query()->with('director.usuario');

        if ($q = $request->query('q')) {
            $query->where('nombre_curso', 'like', "%{$q}%");
        }

        if ($nivel = $request->query('nivel')) {
            $query->where('nivel_academico', $nivel);
        }

        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('estado', $estado);
        }

        return $query->orderByDesc('anio_lectivo')->orderBy('nombre_curso')->paginate(15)->withQueryString();
    }

    private function resumenCursos(): array
    {
        return [
            'total' => Curso::count(),
            'activos' => Curso::where('estado', 'activo')->count(),
            'inactivos' => Curso::where('estado', 'inactivo')->count(),
        ];
    }

    public function store(CursoRequest $request): JsonResponse
    {
        try {
            $curso = Curso::create($request->validated() + ['estado' => 'activo']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un curso con ese nombre, año y jornada.');
        }

        return response()->json(['success' => true, 'message' => 'Curso creado correctamente.', 'id' => $curso->id_curso]);
    }

    public function update(CursoRequest $request, Curso $curso): JsonResponse
    {
        try {
            $curso->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un curso con ese nombre, año y jornada.');
        }

        return response()->json(['success' => true, 'message' => 'Curso actualizado correctamente.']);
    }

    public function desactivar(Curso $curso): JsonResponse
    {
        $curso->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Curso desactivado correctamente.']);
    }

    public function activar(Curso $curso): JsonResponse
    {
        $curso->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Curso reactivado correctamente.']);
    }

    private function respuestaDuplicado(QueryException $e, string $mensaje): JsonResponse
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json(['success' => false, 'message' => $mensaje], 409);
        }

        throw $e;
    }
}
