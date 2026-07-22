<?php

namespace App\Modules\Rector\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Asistencia\Models\Asistencia;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsistenciaController extends Controller
{
    public function show(Request $request, AsignacionAcademica $asignacion): View
    {
        abort_unless($request->user()->puedeGestionarAsignacion($asignacion), 403);

        $asignacion->load(['materia', 'curso', 'profesor.usuario']);
        $fecha = $request->query('fecha', now()->toDateString());

        $estudiantes = $asignacion->curso->matriculas()
            ->where('estado_matricula', 'activa')
            ->with('estudiante.usuario')
            ->get()
            ->pluck('estudiante');

        $registrosExistentes = Asistencia::where('id_asignacion', $asignacion->id_asignacion)
            ->where('fecha', $fecha)
            ->get()
            ->keyBy('id_estudiante');

        return view('Rector.asistencia.show', [
            'currentPage' => 'Asistencia',
            'asignacion' => $asignacion,
            'fecha' => $fecha,
            'estudiantes' => $estudiantes,
            'registrosExistentes' => $registrosExistentes,
        ]);
    }

    public function guardar(Request $request, AsignacionAcademica $asignacion): JsonResponse
    {
        abort_unless($request->user()->puedeGestionarAsignacion($asignacion), 403);

        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'registros' => ['required', 'array'],
            'registros.*.id_estudiante' => ['required', 'integer', 'exists:estudiantes,id_estudiante'],
            'registros.*.estado_asistencia' => ['required', Rule::in(Asistencia::ESTADOS)],
            'registros.*.observacion' => ['nullable', 'string', 'max:300'],
        ]);

        foreach ($data['registros'] as $fila) {
            Asistencia::updateOrCreate(
                [
                    'id_asignacion' => $asignacion->id_asignacion,
                    'id_estudiante' => $fila['id_estudiante'],
                    'fecha' => $data['fecha'],
                ],
                [
                    'estado_asistencia' => $fila['estado_asistencia'],
                    'observacion' => $fila['observacion'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Asistencia guardada correctamente.']);
    }

    public function historial(Request $request, AsignacionAcademica $asignacion): View
    {
        abort_unless($request->user()->puedeGestionarAsignacion($asignacion), 403);

        $resumen = Asistencia::where('id_asignacion', $asignacion->id_asignacion)
            ->selectRaw('fecha, COUNT(*) as total, SUM(estado_asistencia = "presente") as presentes, SUM(estado_asistencia = "ausente") as ausentes, SUM(estado_asistencia = "tarde") as tardes, SUM(estado_asistencia = "excusa") as excusas')
            ->groupBy('fecha')
            ->orderByDesc('fecha')
            ->get();

        return view('Rector.asistencia.historial', [
            'currentPage' => 'Asistencia',
            'asignacion' => $asignacion->load(['materia', 'curso']),
            'resumen' => $resumen,
        ]);
    }
}
