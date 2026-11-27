<?php

namespace App\Modules\Usuarios\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Auth\Services\RevocacionDeSesiones;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Instituciones\Support\TenantContext;
use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListadosController extends Controller
{
    /** id_rol considerados "personal administrativo" (no hay tabla propia, son usuarios de estos roles). */
    private const ROLES_ADMINISTRATIVOS = [1, 2, 3, 4];

    public function estudiantes(Request $request): View
    {
        return view('Rector.usuarios.listados.index', [
            'currentPage' => 'ListadosEstudiantes',
            'tab' => 'estudiantes',
            'titulo' => 'Estudiantes',
            'filtros' => $request->only(['q', 'estado', 'curso']),
            'cursos' => Curso::deInstitucion()->orderBy('nivel_academico')->orderBy('nombre_curso')->get(),
            'estudiantes' => $this->buscarEstudiantes($request),
            'resumenEstudiantes' => $this->resumenEstudiantes(),
            'idsEnRiesgo' => Estudiante::idsEnRiesgo(),
        ]);
    }

    public function docentes(Request $request): View
    {
        return view('Rector.usuarios.listados.index', [
            'currentPage' => 'ListadosDocentes',
            'tab' => 'docentes',
            'titulo' => 'Docentes',
            'filtros' => $request->only(['q', 'estado']),
            'docentes' => $this->buscarDocentes($request),
            'resumenDocentes' => $this->resumenDocentes(),
        ]);
    }

    public function administrativos(Request $request): View
    {
        return view('Rector.usuarios.listados.index', [
            'currentPage' => 'ListadosAdministrativos',
            'tab' => 'administrativos',
            'titulo' => 'Administrativos',
            'filtros' => $request->only(['q', 'estado', 'rol']),
            'administrativos' => $this->buscarAdministrativos($request),
            'resumenAdministrativos' => $this->resumenAdministrativos(),
        ]);
    }

    private function buscarEstudiantes(Request $request)
    {
        $query = Estudiante::query()->deInstitucion()
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
            'total' => Estudiante::deInstitucion()->count(),
            'activos' => Estudiante::deInstitucion()->where('estado_academico', 'activo')->count(),
            'inactivos' => Estudiante::deInstitucion()->where('estado_academico', 'inactivo')->count(),
            'enRiesgo' => Estudiante::idsEnRiesgo()->count(),
        ];
    }

    private function buscarDocentes(Request $request)
    {
        $query = Profesor::query()->deInstitucion()->with(['usuario', 'asignaciones.materia', 'asignaciones.curso']);

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
            'total' => Profesor::deInstitucion()->count(),
            'activos' => Profesor::deInstitucion()->where('estado_laboral', 'activo')->count(),
            'licencia' => Profesor::deInstitucion()->where('estado_laboral', 'licencia')->count(),
            'inactivos' => Profesor::deInstitucion()->whereIn('estado_laboral', ['retirado', 'vacaciones'])->count(),
        ];
    }

    private function buscarAdministrativos(Request $request)
    {
        $query = Usuario::query()->deInstitucion()->whereIn('id_rol', self::ROLES_ADMINISTRATIVOS);

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
        $base = Usuario::deInstitucion()->whereIn('id_rol', self::ROLES_ADMINISTRATIVOS);

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
    public function desactivarEstudiante(Estudiante $estudiante, RevocacionDeSesiones $revocacion): JsonResponse
    {
        TenantContext::asegurar($estudiante->usuario?->id_institucion);

        $estudiante->update(['estado_academico' => 'inactivo']);
        $estudiante->usuario?->update(['estado_usuario' => 'inactivo']);
        // Fase 1 (P1-1): sus sesiones y cookies «recordarme» dejan de valer, aunque se reactive.
        if ($estudiante->usuario) {
            $revocacion->usuarios($estudiante->usuario->id_usuario);
        }

        return response()->json(['success' => true, 'message' => 'Estudiante desactivado correctamente.']);
    }

    public function desactivarDocente(Profesor $profesor, RevocacionDeSesiones $revocacion): JsonResponse
    {
        TenantContext::asegurar($profesor->usuario?->id_institucion);

        $profesor->update(['estado_laboral' => 'retirado']);
        $profesor->usuario?->update(['estado_usuario' => 'inactivo']);
        if ($profesor->usuario) {
            $revocacion->usuarios($profesor->usuario->id_usuario);
        }

        return response()->json(['success' => true, 'message' => 'Docente desactivado correctamente.']);
    }

    public function desactivarAdministrativo(Usuario $usuario, RevocacionDeSesiones $revocacion): JsonResponse
    {
        $this->asegurarAdministrativoPropio($usuario);

        $usuario->update(['estado_usuario' => 'inactivo']);
        $revocacion->usuarios($usuario->id_usuario);

        return response()->json(['success' => true, 'message' => 'Usuario desactivado correctamente.']);
    }

    public function activarEstudiante(Estudiante $estudiante): JsonResponse
    {
        TenantContext::asegurar($estudiante->usuario?->id_institucion);

        $estudiante->update(['estado_academico' => 'activo']);
        $estudiante->usuario?->update(['estado_usuario' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Estudiante reactivado correctamente.']);
    }

    public function activarDocente(Profesor $profesor): JsonResponse
    {
        TenantContext::asegurar($profesor->usuario?->id_institucion);

        $profesor->update(['estado_laboral' => 'activo']);
        $profesor->usuario?->update(['estado_usuario' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Docente reactivado correctamente.']);
    }

    public function activarAdministrativo(Usuario $usuario): JsonResponse
    {
        $this->asegurarAdministrativoPropio($usuario);

        $usuario->update(['estado_usuario' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Usuario reactivado correctamente.']);
    }

    /**
     * Solo personal administrativo (roles 1–4) de la propia institución:
     * este endpoint no puede (des)activar SuperAdmin, estudiantes, docentes,
     * acudientes ni cuentas de otra institución (404).
     */
    private function asegurarAdministrativoPropio(Usuario $usuario): void
    {
        TenantContext::asegurar($usuario->id_institucion);
        abort_unless(in_array((int) $usuario->id_rol, self::ROLES_ADMINISTRATIVOS, true), 404);
    }
}
