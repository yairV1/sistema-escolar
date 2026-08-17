<?php

namespace App\Modules\Calificaciones\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calificaciones\Models\Actividad;
use App\Modules\Calificaciones\Models\Nota;
use App\Modules\Calificaciones\Models\Periodo;
use App\Modules\Calificaciones\Models\TipoActividad;
use App\Modules\Calificaciones\Requests\ActividadRequest;
use App\Modules\Calificaciones\Requests\PeriodoRequest;
use App\Modules\Calificaciones\Requests\TipoActividadRequest;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CalificacionesController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'periodos');
        $tab = in_array($tab, ['periodos', 'tipos-actividad'], true) ? $tab : 'periodos';

        return view('Rector.calificaciones.index', [
            'currentPage' => 'Calificaciones',
            'tab' => $tab,
            'filtros' => $request->only(['q', 'estado', 'anio']),
            'periodos' => $tab === 'periodos' ? $this->buscarPeriodos($request) : null,
            'resumenPeriodos' => $tab === 'periodos' ? $this->resumenPeriodos() : null,
            'tiposActividad' => $tab === 'tipos-actividad' ? $this->buscarTiposActividad($request) : null,
            'resumenTiposActividad' => $tab === 'tipos-actividad' ? $this->resumenTiposActividad() : null,
        ]);
    }

    private function buscarPeriodos(Request $request)
    {
        $query = Periodo::query();

        if ($q = $request->query('q')) {
            $query->where('nombre_periodo', 'like', "%{$q}%");
        }

        if ($anio = $request->query('anio')) {
            $query->where('anio_lectivo', $anio);
        }

        $estado = $request->query('estado');
        if (in_array($estado, Periodo::ESTADOS, true)) {
            $query->where('estado', $estado);
        }

        return $query->orderByDesc('anio_lectivo')->orderBy('fecha_inicio')->paginate(15)->withQueryString();
    }

    private function resumenPeriodos(): array
    {
        return [
            'total' => Periodo::count(),
            'activos' => Periodo::where('estado', 'activo')->count(),
            'cerrados' => Periodo::where('estado', 'cerrado')->count(),
        ];
    }

    private function buscarTiposActividad(Request $request)
    {
        $query = TipoActividad::query();

        if ($q = $request->query('q')) {
            $query->where('nombre_tipo', 'like', "%{$q}%");
        }

        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('nombre_tipo')->paginate(15)->withQueryString();
    }

    private function resumenTiposActividad(): array
    {
        return [
            'total' => TipoActividad::count(),
            'activos' => TipoActividad::where('estado', 'activo')->count(),
            'inactivos' => TipoActividad::where('estado', 'inactivo')->count(),
        ];
    }

    public function asignacion(Request $request, AsignacionAcademica $asignacion): View
    {
        abort_unless($request->user()->puedeGestionarAsignacion($asignacion), 403);

        $asignacion->load(['materia', 'curso', 'profesor.usuario']);

        $query = $asignacion->actividades()->with(['periodo', 'tipo']);

        if ($idPeriodo = $request->query('periodo')) {
            $query->where('id_periodo', $idPeriodo);
        }

        return view('Rector.calificaciones.asignacion', [
            'currentPage' => 'Calificaciones',
            'asignacion' => $asignacion,
            'actividades' => $query->orderByDesc('fecha_creacion')->get(),
            'periodos' => Periodo::orderByDesc('anio_lectivo')->orderBy('fecha_inicio')->get(),
            'tiposActividad' => TipoActividad::where('estado', 'activo')->orderBy('nombre_tipo')->get(),
            'filtroPeriodo' => $idPeriodo,
        ]);
    }

    public function notas(Request $request, Actividad $actividad): View
    {
        $actividad->load(['asignacion.curso', 'asignacion.materia', 'periodo', 'tipo']);

        abort_unless($request->user()->puedeGestionarAsignacion($actividad->asignacion), 403);

        $estudiantes = $actividad->asignacion->curso->matriculas()
            ->where('estado_matricula', 'activa')
            ->with('estudiante.usuario')
            ->get()
            ->pluck('estudiante');

        $notasExistentes = Nota::where('id_actividad', $actividad->id_actividad)->get()->keyBy('id_estudiante');

        return view('Rector.calificaciones.notas', [
            'currentPage' => 'Calificaciones',
            'actividad' => $actividad,
            'estudiantes' => $estudiantes,
            'notasExistentes' => $notasExistentes,
        ]);
    }

    public function planilla(Request $request, AsignacionAcademica $asignacion): View
    {
        abort_unless($request->user()->puedeGestionarAsignacion($asignacion), 403);

        $asignacion->load(['materia', 'curso', 'profesor.usuario']);

        $idPeriodo = $request->query('periodo') ? (int) $request->query('periodo') : null;

        return view('Rector.calificaciones.planilla', [
            'currentPage' => 'Calificaciones',
            'asignacion' => $asignacion,
            'periodos' => Periodo::orderByDesc('anio_lectivo')->orderBy('fecha_inicio')->get(),
            'filtroPeriodo' => $idPeriodo,
            ...$this->construirPlanilla($asignacion, $idPeriodo),
        ]);
    }

    public function exportarPlanilla(Request $request, AsignacionAcademica $asignacion): BinaryFileResponse
    {
        abort_unless($request->user()->puedeGestionarAsignacion($asignacion), 403);

        $asignacion->load(['materia', 'curso']);

        $idPeriodo = $request->query('periodo') ? (int) $request->query('periodo') : null;
        $datos = $this->construirPlanilla($asignacion, $idPeriodo);

        $filas = [];
        $encabezado = ['Código', 'Estudiante'];
        foreach ($datos['actividades'] as $actividad) {
            $encabezado[] = $actividad->titulo.' ('.rtrim(rtrim(number_format($actividad->porcentaje, 2), '0'), '.').'% · '.$actividad->periodo->nombre_periodo.')';
        }
        if ($idPeriodo) {
            $encabezado[] = 'Definitiva';
        }
        $filas[] = $encabezado;

        foreach ($datos['estudiantes'] as $estudiante) {
            $fila = [
                $estudiante->codigo_estudiante,
                trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos),
            ];
            foreach ($datos['actividades'] as $actividad) {
                $fila[] = $datos['notas'][$estudiante->id_estudiante][$actividad->id_actividad] ?? '';
            }
            if ($idPeriodo) {
                $fila[] = $datos['definitivas'][$estudiante->id_estudiante] ?? '';
            }
            $filas[] = $fila;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Planilla');
        $sheet->fromArray($filas, null, 'A1');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
        if ($idPeriodo) {
            $sheet->getStyle($sheet->getHighestColumn().'1:'.$sheet->getHighestColumn().$sheet->getHighestRow())->getFont()->setBold(true);
        }
        foreach (range('A', $sheet->getHighestColumn()) as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }

        $nombre = 'planilla-'.\Illuminate\Support\Str::slug($asignacion->materia->nombre_materia.'-'.$asignacion->curso->nombre_curso).'.xlsx';
        $ruta = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('planilla_', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($ruta);

        return response()->download($ruta, $nombre)->deleteFileAfterSend(true);
    }

    private function construirPlanilla(AsignacionAcademica $asignacion, ?int $idPeriodo): array
    {
        $query = $asignacion->actividades()->where('estado', 'activa')->with(['periodo', 'tipo']);

        if ($idPeriodo) {
            $query->where('id_periodo', $idPeriodo);
        }

        $actividades = $query->orderBy('fecha_creacion')->get();

        $estudiantes = $asignacion->curso->matriculas()
            ->where('estado_matricula', 'activa')
            ->with('estudiante.usuario')
            ->get()
            ->pluck('estudiante');

        $todasLasNotas = Nota::whereIn('id_actividad', $actividades->pluck('id_actividad'))->get();

        $notas = [];
        foreach ($todasLasNotas as $nota) {
            $notas[$nota->id_estudiante][$nota->id_actividad] = $nota->nota;
        }

        $definitivas = [];
        if ($idPeriodo) {
            foreach ($estudiantes as $estudiante) {
                $sumaPonderada = 0.0;
                $sumaPorcentajes = 0.0;

                foreach ($actividades as $actividad) {
                    $nota = $notas[$estudiante->id_estudiante][$actividad->id_actividad] ?? null;

                    if ($nota !== null) {
                        $sumaPonderada += $nota * $actividad->porcentaje;
                        $sumaPorcentajes += $actividad->porcentaje;
                    }
                }

                $definitivas[$estudiante->id_estudiante] = $sumaPorcentajes > 0 ? round($sumaPonderada / $sumaPorcentajes, 2) : null;
            }
        }

        return [
            'actividades' => $actividades,
            'estudiantes' => $estudiantes,
            'notas' => $notas,
            'definitivas' => $definitivas,
        ];
    }

    public function guardarNotas(Request $request, Actividad $actividad): JsonResponse
    {
        abort_unless($request->user()->puedeGestionarAsignacion($actividad->asignacion), 403);

        $data = $request->validate([
            'notas' => ['required', 'array'],
            'notas.*.id_estudiante' => ['required', 'integer', 'exists:estudiantes,id_estudiante'],
            'notas.*.nota' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ]);

        foreach ($data['notas'] as $fila) {
            Nota::updateOrCreate(
                ['id_actividad' => $actividad->id_actividad, 'id_estudiante' => $fila['id_estudiante']],
                ['nota' => $fila['nota'], 'estado' => $fila['nota'] !== null ? 'registrada' : 'pendiente']
            );
        }

        return response()->json(['success' => true, 'message' => 'Notas guardadas correctamente.']);
    }

    public function storePeriodo(PeriodoRequest $request): JsonResponse
    {
        try {
            $periodo = Periodo::create($request->validated() + ['estado' => $request->validated()['estado'] ?? 'pendiente']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un periodo con ese nombre para ese año lectivo.');
        }

        return response()->json(['success' => true, 'message' => 'Periodo creado correctamente.', 'id' => $periodo->id_periodo]);
    }

    public function updatePeriodo(PeriodoRequest $request, Periodo $periodo): JsonResponse
    {
        try {
            $periodo->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un periodo con ese nombre para ese año lectivo.');
        }

        return response()->json(['success' => true, 'message' => 'Periodo actualizado correctamente.']);
    }

    public function desactivarPeriodo(Periodo $periodo): JsonResponse
    {
        $periodo->update(['estado' => 'cerrado']);

        return response()->json(['success' => true, 'message' => 'Periodo cerrado correctamente.']);
    }

    public function activarPeriodo(Periodo $periodo): JsonResponse
    {
        $periodo->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Periodo reactivado correctamente.']);
    }

    public function storeTipoActividad(TipoActividadRequest $request): JsonResponse
    {
        try {
            $tipo = TipoActividad::create($request->validated() + ['estado' => 'activo']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un tipo de actividad con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Tipo de actividad creado correctamente.', 'id' => $tipo->id_tipo_actividad]);
    }

    public function updateTipoActividad(TipoActividadRequest $request, TipoActividad $tipoActividad): JsonResponse
    {
        try {
            $tipoActividad->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un tipo de actividad con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Tipo de actividad actualizado correctamente.']);
    }

    public function desactivarTipoActividad(TipoActividad $tipoActividad): JsonResponse
    {
        $tipoActividad->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Tipo de actividad desactivado correctamente.']);
    }

    public function activarTipoActividad(TipoActividad $tipoActividad): JsonResponse
    {
        $tipoActividad->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Tipo de actividad reactivado correctamente.']);
    }

    public function storeActividad(ActividadRequest $request, AsignacionAcademica $asignacion): JsonResponse
    {
        abort_unless($request->user()->puedeGestionarAsignacion($asignacion), 403);

        try {
            $actividad = Actividad::create($request->validated() + ['id_asignacion' => $asignacion->id_asignacion, 'estado' => 'activa']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe una actividad así para esta asignación.');
        }

        return response()->json(['success' => true, 'message' => 'Actividad creada correctamente.', 'id' => $actividad->id_actividad]);
    }

    public function updateActividad(ActividadRequest $request, Actividad $actividad): JsonResponse
    {
        abort_unless($request->user()->puedeGestionarAsignacion($actividad->asignacion), 403);

        try {
            $actividad->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe una actividad así para esta asignación.');
        }

        return response()->json(['success' => true, 'message' => 'Actividad actualizada correctamente.']);
    }

    public function desactivarActividad(Actividad $actividad): JsonResponse
    {
        abort_unless(auth()->user()->puedeGestionarAsignacion($actividad->asignacion), 403);

        $actividad->update(['estado' => 'anulada']);

        return response()->json(['success' => true, 'message' => 'Actividad anulada correctamente.']);
    }

    public function activarActividad(Actividad $actividad): JsonResponse
    {
        abort_unless(auth()->user()->puedeGestionarAsignacion($actividad->asignacion), 403);

        $actividad->update(['estado' => 'activa']);

        return response()->json(['success' => true, 'message' => 'Actividad reactivada correctamente.']);
    }

    private function respuestaDuplicado(QueryException $e, string $mensaje): JsonResponse
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json(['success' => false, 'message' => $mensaje], 409);
        }

        throw $e;
    }
}
