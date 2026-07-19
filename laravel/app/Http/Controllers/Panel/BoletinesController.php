<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Boletin;
use App\Models\BoletinDetalle;
use App\Models\Curso;
use App\Models\Nota;
use App\Models\Periodo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoletinesController extends Controller
{
    public function index(Request $request): View
    {
        $idCurso = $request->query('curso');
        $idPeriodo = $request->query('periodo');

        $boletines = collect();
        if ($idCurso && $idPeriodo) {
            $boletines = Boletin::where('id_periodo', $idPeriodo)
                ->whereHas('estudiante.matriculas', fn ($q) => $q->where('id_curso', $idCurso)->where('estado_matricula', 'activa'))
                ->with('estudiante.usuario')
                ->orderByRaw('promedio_general IS NULL, promedio_general DESC')
                ->get();
        }

        return view('panel.boletines.index', [
            'currentPage' => 'Boletines',
            'cursos' => Curso::where('estado', 'activo')->orderBy('nombre_curso')->get(),
            'periodos' => Periodo::orderByDesc('anio_lectivo')->orderBy('fecha_inicio')->get(),
            'idCurso' => $idCurso,
            'idPeriodo' => $idPeriodo,
            'boletines' => $boletines,
        ]);
    }

    public function generar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'id_periodo' => ['required', 'integer', 'exists:periodos_academicos,id_periodo'],
        ]);

        $curso = Curso::findOrFail($data['id_curso']);
        $idPeriodo = (int) $data['id_periodo'];

        $idsEstudiantes = $curso->matriculas()->where('estado_matricula', 'activa')->pluck('id_estudiante');
        $asignaciones = $curso->asignaciones()->where('estado', 'activo')->get();

        $promedios = [];

        foreach ($idsEstudiantes as $idEstudiante) {
            $boletin = Boletin::firstOrCreate(
                ['id_estudiante' => $idEstudiante, 'id_periodo' => $idPeriodo],
                ['estado' => 'borrador']
            );

            $notasMaterias = [];

            foreach ($asignaciones as $asignacion) {
                $actividades = $asignacion->actividades()
                    ->where('id_periodo', $idPeriodo)
                    ->where('estado', 'activa')
                    ->get();

                $sumaPonderada = 0.0;
                $sumaPorcentajes = 0.0;

                foreach ($actividades as $actividad) {
                    $nota = Nota::where('id_actividad', $actividad->id_actividad)
                        ->where('id_estudiante', $idEstudiante)
                        ->value('nota');

                    if ($nota !== null) {
                        $sumaPonderada += $nota * $actividad->porcentaje;
                        $sumaPorcentajes += $actividad->porcentaje;
                    }
                }

                $notaDefinitiva = $sumaPorcentajes > 0 ? round($sumaPonderada / $sumaPorcentajes, 2) : null;

                BoletinDetalle::updateOrCreate(
                    ['id_boletin' => $boletin->id_boletin, 'id_asignacion' => $asignacion->id_asignacion],
                    ['nota_definitiva' => $notaDefinitiva]
                );

                if ($notaDefinitiva !== null) {
                    $notasMaterias[] = $notaDefinitiva;
                }
            }

            $promedioGeneral = count($notasMaterias) > 0 ? round(array_sum($notasMaterias) / count($notasMaterias), 2) : null;
            $boletin->update(['promedio_general' => $promedioGeneral]);
            $promedios[$idEstudiante] = $promedioGeneral;
        }

        $conPromedio = array_filter($promedios, fn ($v) => $v !== null);
        arsort($conPromedio);
        $puesto = 1;
        foreach ($conPromedio as $idEstudiante => $promedio) {
            Boletin::where('id_estudiante', $idEstudiante)->where('id_periodo', $idPeriodo)->update(['puesto_curso' => $puesto]);
            $puesto++;
        }

        return response()->json(['success' => true, 'message' => 'Boletines generados correctamente.']);
    }

    public function show(Boletin $boletin): View
    {
        $boletin->load(['estudiante.usuario', 'periodo', 'detalle.asignacion.materia', 'detalle.asignacion.profesor.usuario']);

        return view('panel.boletines.show', [
            'currentPage' => 'Boletines',
            'boletin' => $boletin,
        ]);
    }

    public function publicar(Boletin $boletin): JsonResponse
    {
        $boletin->update(['estado' => 'publicado']);

        return response()->json(['success' => true, 'message' => 'Boletín publicado correctamente.']);
    }

    public function anular(Boletin $boletin): JsonResponse
    {
        $boletin->update(['estado' => 'anulado']);

        return response()->json(['success' => true, 'message' => 'Boletín anulado correctamente.']);
    }

    public function volverBorrador(Boletin $boletin): JsonResponse
    {
        $boletin->update(['estado' => 'borrador']);

        return response()->json(['success' => true, 'message' => 'Boletín devuelto a borrador.']);
    }
}
