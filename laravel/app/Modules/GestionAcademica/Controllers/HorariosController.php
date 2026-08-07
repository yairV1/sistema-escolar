<?php

namespace App\Modules\GestionAcademica\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\GestionAcademica\Models\Horario;
use App\Modules\GestionAcademica\Requests\HorarioRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HorariosController extends Controller
{
    public function index(Request $request): View
    {
        $cursosParaHorario = Curso::where('estado', 'activo')->orderByDesc('anio_lectivo')->orderBy('nombre_curso')->get();
        $cursoHorarioId = (int) ($request->query('curso') ?: optional($cursosParaHorario->first())->id_curso);

        return view('Rector.gestion-academica.horarios.index', [
            'currentPage' => 'GestionAcademicaHorarios',
            'cursosParaHorario' => $cursosParaHorario,
            'cursoHorarioId' => $cursoHorarioId,
            'asignacionesHorario' => $cursoHorarioId
                ? AsignacionAcademica::where('id_curso', $cursoHorarioId)->where('estado', 'activo')->with(['materia', 'profesor.usuario'])->get()
                : collect(),
            'horariosCurso' => $cursoHorarioId
                ? Horario::whereHas('asignacion', fn ($q) => $q->where('id_curso', $cursoHorarioId))
                    ->where('estado', 'activo')
                    ->with(['asignacion.materia', 'asignacion.profesor.usuario'])
                    ->orderByRaw("FIELD(dia_semana,'lunes','martes','miercoles','jueves','viernes','sabado')")
                    ->orderBy('hora_inicio')
                    ->get()
                : collect(),
        ]);
    }

    public function store(HorarioRequest $request): JsonResponse
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

    public function update(HorarioRequest $request, Horario $horario): JsonResponse
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

    public function desactivar(Horario $horario): JsonResponse
    {
        $horario->update(['estado' => 'inactivo']);

        $this->invalidarCacheCalendario();

        return response()->json(['success' => true, 'message' => 'Horario desactivado correctamente.']);
    }

    public function activar(Horario $horario): JsonResponse
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
