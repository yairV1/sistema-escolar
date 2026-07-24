<?php

namespace App\Modules\GestionAcademica\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\GestionAcademica\Models\Horario;
use App\Modules\GestionAcademica\Models\Materia;
use App\Modules\GestionAcademica\Requests\AsignacionRequest;
use App\Modules\GestionAcademica\Requests\CursoRequest;
use App\Modules\GestionAcademica\Requests\HorarioRequest;
use App\Modules\GestionAcademica\Requests\MateriaRequest;
use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GestionAcademicaController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'materias');
        $tab = in_array($tab, ['materias', 'cursos', 'asignaciones', 'horarios'], true) ? $tab : 'materias';

        $cursosParaHorario = $tab === 'horarios' ? Curso::where('estado', 'activo')->orderByDesc('anio_lectivo')->orderBy('nombre_curso')->get() : null;
        $cursoHorarioId = $tab === 'horarios' ? (int) ($request->query('curso') ?: optional($cursosParaHorario->first())->id_curso) : null;

        return view('Rector.gestion-academica.index', [
            'currentPage' => 'GestionAcademica',
            'tab' => $tab,
            'filtros' => $request->only(['q', 'estado', 'curso', 'anio', 'nivel']),
            'profesores' => Profesor::with('usuario')->where('estado_laboral', 'activo')->get(),
            'todasLasMaterias' => $tab === 'asignaciones' ? Materia::where('estado', 'activo')->orderBy('nombre_materia')->get() : null,
            'todosLosCursos' => $tab === 'asignaciones' ? Curso::where('estado', 'activo')->orderBy('nombre_curso')->get() : null,
            'materias' => $tab === 'materias' ? $this->buscarMaterias($request) : null,
            'resumenMaterias' => $tab === 'materias' ? $this->resumenMaterias() : null,
            'cursos' => $tab === 'cursos' ? $this->buscarCursos($request) : null,
            'resumenCursos' => $tab === 'cursos' ? $this->resumenCursos() : null,
            'asignaciones' => $tab === 'asignaciones' ? $this->buscarAsignaciones($request) : null,
            'resumenAsignaciones' => $tab === 'asignaciones' ? $this->resumenAsignaciones() : null,
            'cursosParaHorario' => $cursosParaHorario,
            'cursoHorarioId' => $cursoHorarioId,
            'asignacionesHorario' => $tab === 'horarios' && $cursoHorarioId
                ? AsignacionAcademica::where('id_curso', $cursoHorarioId)->where('estado', 'activo')->with(['materia', 'profesor.usuario'])->get()
                : collect(),
            'horariosCurso' => $tab === 'horarios' && $cursoHorarioId
                ? Horario::whereHas('asignacion', fn ($q) => $q->where('id_curso', $cursoHorarioId))
                    ->where('estado', 'activo')
                    ->with(['asignacion.materia', 'asignacion.profesor.usuario'])
                    ->orderByRaw("FIELD(dia_semana,'lunes','martes','miercoles','jueves','viernes','sabado')")
                    ->orderBy('hora_inicio')
                    ->get()
                : collect(),
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
            'currentPage' => 'GestionAcademica',
            'curso' => $curso,
            'estudiantes' => $estudiantes,
            'asignaciones' => $asignaciones,
            'horarios' => $horarios,
            'idsEnRiesgo' => Estudiante::idsEnRiesgo(),
        ]);
    }

    private function buscarMaterias(Request $request)
    {
        $query = Materia::query();

        if ($q = $request->query('q')) {
            $query->where('nombre_materia', 'like', "%{$q}%");
        }

        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('nombre_materia')->paginate(15)->withQueryString();
    }

    private function resumenMaterias(): array
    {
        return [
            'total' => Materia::count(),
            'activas' => Materia::where('estado', 'activo')->count(),
            'inactivas' => Materia::where('estado', 'inactivo')->count(),
        ];
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

    public function storeMateria(MateriaRequest $request): JsonResponse
    {
        try {
            $materia = Materia::create($request->validated() + ['estado' => 'activo']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe una materia con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Materia creada correctamente.', 'id' => $materia->id_materia]);
    }

    public function updateMateria(MateriaRequest $request, Materia $materia): JsonResponse
    {
        try {
            $materia->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe una materia con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Materia actualizada correctamente.']);
    }

    public function desactivarMateria(Materia $materia): JsonResponse
    {
        $materia->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Materia desactivada correctamente.']);
    }

    public function activarMateria(Materia $materia): JsonResponse
    {
        $materia->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Materia reactivada correctamente.']);
    }

    public function storeCurso(CursoRequest $request): JsonResponse
    {
        try {
            $curso = Curso::create($request->validated() + ['estado' => 'activo']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un curso con ese nombre, año y jornada.');
        }

        return response()->json(['success' => true, 'message' => 'Curso creado correctamente.', 'id' => $curso->id_curso]);
    }

    public function updateCurso(CursoRequest $request, Curso $curso): JsonResponse
    {
        try {
            $curso->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un curso con ese nombre, año y jornada.');
        }

        return response()->json(['success' => true, 'message' => 'Curso actualizado correctamente.']);
    }

    public function desactivarCurso(Curso $curso): JsonResponse
    {
        $curso->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Curso desactivado correctamente.']);
    }

    public function activarCurso(Curso $curso): JsonResponse
    {
        $curso->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Curso reactivado correctamente.']);
    }

    public function storeAsignacion(AsignacionRequest $request): JsonResponse
    {
        try {
            $asignacion = AsignacionAcademica::create($request->validated() + ['estado' => 'activo']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ese profesor ya tiene asignada esa materia en ese curso y año.');
        }

        return response()->json(['success' => true, 'message' => 'Asignación creada correctamente.', 'id' => $asignacion->id_asignacion]);
    }

    public function desactivarAsignacion(AsignacionAcademica $asignacion): JsonResponse
    {
        $asignacion->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Asignación desactivada correctamente.']);
    }

    public function activarAsignacion(AsignacionAcademica $asignacion): JsonResponse
    {
        $asignacion->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Asignación reactivada correctamente.']);
    }

    public function storeHorario(HorarioRequest $request): JsonResponse
    {
        if ($conflicto = $this->validarSolapamientoHorario($request)) {
            return $conflicto;
        }

        try {
            $horario = Horario::create($request->validated() + ['estado' => 'activo']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe ese bloque horario para esa asignación.');
        }

        $this->invalidarCacheCalendario();

        return response()->json(['success' => true, 'message' => 'Horario creado correctamente.', 'id' => $horario->id_horario]);
    }

    public function updateHorario(HorarioRequest $request, Horario $horario): JsonResponse
    {
        if ($conflicto = $this->validarSolapamientoHorario($request, $horario->id_horario)) {
            return $conflicto;
        }

        try {
            $horario->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe ese bloque horario para esa asignación.');
        }

        $this->invalidarCacheCalendario();

        return response()->json(['success' => true, 'message' => 'Horario actualizado correctamente.']);
    }

    public function desactivarHorario(Horario $horario): JsonResponse
    {
        $horario->update(['estado' => 'inactivo']);

        $this->invalidarCacheCalendario();

        return response()->json(['success' => true, 'message' => 'Horario desactivado correctamente.']);
    }

    public function activarHorario(Horario $horario): JsonResponse
    {
        $horario->update(['estado' => 'activo']);

        $this->invalidarCacheCalendario();

        return response()->json(['success' => true, 'message' => 'Horario reactivado correctamente.']);
    }

    /**
     * Bump de la versión que usa HorarioOccurrenceSource (módulo Calendario)
     * como parte de su clave de caché — cualquier mutación de horarios
     * invalida al instante el calendario derivado, sin esperar TTL ni
     * depender de Cache::tags (no soportado por el driver `database`).
     */
    private function invalidarCacheCalendario(): void
    {
        Cache::increment('calendario:horarios_version');
    }

    /**
     * Evita solapamientos de horario en dos sentidos:
     * 1) Un mismo profesor con 2 clases a la vez (en cualquier curso) — error duro,
     *    no tiene sentido "reemplazar" la clase de otro curso.
     * 2) Un mismo curso con 2 materias a la vez — se informa cuál choca y se ofrece
     *    reemplazarla (el frontend desactiva ese horario y reintenta el guardado).
     */
    private function validarSolapamientoHorario(HorarioRequest $request, ?int $ignorarHorarioId = null): ?JsonResponse
    {
        $d = $request->validated();
        $asignacion = AsignacionAcademica::find($d['id_asignacion']);

        $conflictoProfesor = Horario::where('estado', 'activo')
            ->where('dia_semana', $d['dia_semana'])
            ->where('hora_inicio', '<', $d['hora_fin'])
            ->where('hora_fin', '>', $d['hora_inicio'])
            ->whereHas('asignacion', fn ($q) => $q->where('id_profesor', $asignacion?->id_profesor))
            ->when($ignorarHorarioId, fn ($q) => $q->where('id_horario', '!=', $ignorarHorarioId))
            ->with('asignacion.materia', 'asignacion.curso')
            ->first();

        if ($conflictoProfesor) {
            return response()->json([
                'success' => false,
                'message' => "Ese profesor ya tiene clase de {$conflictoProfesor->asignacion->materia->nombre_materia} en {$conflictoProfesor->asignacion->curso->nombre_curso} a esa hora.",
            ], 409);
        }

        $conflictoCurso = Horario::where('estado', 'activo')
            ->where('dia_semana', $d['dia_semana'])
            ->where('hora_inicio', '<', $d['hora_fin'])
            ->where('hora_fin', '>', $d['hora_inicio'])
            ->whereHas('asignacion', fn ($q) => $q->where('id_curso', $asignacion?->id_curso))
            ->when($ignorarHorarioId, fn ($q) => $q->where('id_horario', '!=', $ignorarHorarioId))
            ->with('asignacion.materia')
            ->first();

        if (! $conflictoCurso) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => "Ya hay una clase de {$conflictoCurso->asignacion->materia->nombre_materia} a esa hora. ¿Querés reemplazarla?",
            'conflicto_id' => $conflictoCurso->id_horario,
            'conflicto_desactivar_url' => route('gestion-academica.horarios.desactivar', $conflictoCurso->id_horario),
        ], 409);
    }

    private function respuestaDuplicado(QueryException $e, string $mensaje): JsonResponse
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json(['success' => false, 'message' => $mensaje], 409);
        }

        throw $e;
    }
}
