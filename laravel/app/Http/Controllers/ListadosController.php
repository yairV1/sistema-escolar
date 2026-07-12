<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListadosController extends Controller
{
    /** id_rol considerados "personal administrativo" (no hay tabla propia, son usuarios de estos roles). */
    private const ROLES_ADMINISTRATIVOS = [1, 2, 3, 4];

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'estudiantes');
        $tab = in_array($tab, ['estudiantes', 'docentes', 'administrativos'], true) ? $tab : 'estudiantes';

        return view('listados.index', [
            'currentPage' => 'Listados',
            'tab' => $tab,
            'filtros' => $request->only(['q', 'estado', 'curso', 'rol']),
            'cursos' => Curso::orderBy('nivel_academico')->orderBy('nombre_curso')->get(),
            'estudiantes' => $tab === 'estudiantes' ? $this->buscarEstudiantes($request) : null,
            'resumenEstudiantes' => $tab === 'estudiantes' ? $this->resumenEstudiantes() : null,
            'idsEnRiesgo' => $tab === 'estudiantes' ? Estudiante::idsEnRiesgo() : null,
            'docentes' => $tab === 'docentes' ? $this->buscarDocentes($request) : null,
            'resumenDocentes' => $tab === 'docentes' ? $this->resumenDocentes() : null,
            'administrativos' => $tab === 'administrativos' ? $this->buscarAdministrativos($request) : null,
            'resumenAdministrativos' => $tab === 'administrativos' ? $this->resumenAdministrativos() : null,
        ]);
    }

    private function buscarEstudiantes(Request $request)
    {
        $query = Estudiante::query()
            ->with(['usuario', 'matriculas' => fn ($q) => $q->latest('fecha_matricula')->with('curso')]);

        if ($q = $request->query('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('codigo_estudiante', 'like', "%{$q}%")
                    ->orWhereHas('usuario', function ($uq) use ($q) {
                        $uq->where('nombres', 'like', "%{$q}%")
                            ->orWhere('apellidos', 'like', "%{$q}%")
                            ->orWhere('correo', 'like', "%{$q}%");
                    });
            });
        }

        $estado = $request->query('estado');
        if ($estado === 'riesgo') {
            $query->whereIn('id_estudiante', Estudiante::idsEnRiesgo());
        } elseif (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('estado_academico', $estado);
        }

        if ($curso = $request->query('curso')) {
            $query->whereHas('matriculas', fn ($m) => $m->where('id_curso', $curso));
        }

        return $query->join('usuarios', 'usuarios.id_usuario', '=', 'estudiantes.id_usuario')
            ->orderBy('usuarios.apellidos')
            ->orderBy('usuarios.nombres')
            ->select('estudiantes.*')
            ->paginate(15)
            ->withQueryString();
    }

    private function resumenEstudiantes(): array
    {
        return [
            'total' => Estudiante::count(),
            'activos' => Estudiante::where('estado_academico', 'activo')->count(),
            'inactivos' => Estudiante::where('estado_academico', 'inactivo')->count(),
            'enRiesgo' => Estudiante::idsEnRiesgo()->count(),
        ];
    }

    private function buscarDocentes(Request $request)
    {
        $query = Profesor::query()->with(['usuario', 'asignaciones.materia', 'asignaciones.curso']);

        if ($q = $request->query('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('codigo_profesor', 'like', "%{$q}%")
                    ->orWhereHas('usuario', function ($uq) use ($q) {
                        $uq->where('nombres', 'like', "%{$q}%")
                            ->orWhere('apellidos', 'like', "%{$q}%")
                            ->orWhere('correo', 'like', "%{$q}%");
                    });
            });
        }

        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'licencia', 'retirado', 'vacaciones'], true)) {
            $query->where('estado_laboral', $estado);
        }

        return $query->join('usuarios', 'usuarios.id_usuario', '=', 'profesores.id_usuario')
            ->orderBy('usuarios.apellidos')
            ->orderBy('usuarios.nombres')
            ->select('profesores.*')
            ->paginate(15)
            ->withQueryString();
    }

    private function resumenDocentes(): array
    {
        return [
            'total' => Profesor::count(),
            'activos' => Profesor::where('estado_laboral', 'activo')->count(),
            'licencia' => Profesor::where('estado_laboral', 'licencia')->count(),
            'inactivos' => Profesor::whereIn('estado_laboral', ['retirado', 'vacaciones'])->count(),
        ];
    }

    private function buscarAdministrativos(Request $request)
    {
        $query = Usuario::query()->whereIn('id_rol', self::ROLES_ADMINISTRATIVOS);

        if ($q = $request->query('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('nombres', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('numero_documento', 'like', "%{$q}%");
            });
        }

        if ($rol = $request->query('rol')) {
            $query->where('id_rol', $rol);
        }

        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'inactivo', 'bloqueado'], true)) {
            $query->where('estado_usuario', $estado);
        }

        return $query->orderBy('apellidos')->orderBy('nombres')->paginate(15)->withQueryString();
    }

    private function resumenAdministrativos(): array
    {
        $base = Usuario::whereIn('id_rol', self::ROLES_ADMINISTRATIVOS);

        return [
            'total' => (clone $base)->count(),
            'activos' => (clone $base)->where('estado_usuario', 'activo')->count(),
            'inactivos' => (clone $base)->whereIn('estado_usuario', ['inactivo', 'bloqueado'])->count(),
        ];
    }

    /**
     * Baja lógica (mismo comportamiento que EstudianteController::eliminar()
     * del legacy). Se reimplementa nativa en Laravel porque el endpoint
     * legacy exige la sesión PHP del sistema legacy, que un usuario
     * autenticado en Laravel no tiene.
     */
    public function desactivarEstudiante(Estudiante $estudiante): JsonResponse
    {
        $estudiante->update(['estado_academico' => 'inactivo']);
        $estudiante->usuario?->update(['estado_usuario' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Estudiante desactivado correctamente.']);
    }

    public function desactivarDocente(Profesor $profesor): JsonResponse
    {
        $profesor->update(['estado_laboral' => 'retirado']);
        $profesor->usuario?->update(['estado_usuario' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Docente desactivado correctamente.']);
    }

    public function desactivarAdministrativo(Usuario $usuario): JsonResponse
    {
        $usuario->update(['estado_usuario' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Usuario desactivado correctamente.']);
    }
}
