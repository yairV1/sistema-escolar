<?php

namespace App\Modules\Reportes\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calificaciones\Models\Nota;
use App\Modules\Calificaciones\Models\Periodo;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Matriculas\Models\Matricula;
use App\Modules\Reportes\Models\Boletin;
use App\Modules\Reportes\Models\BoletinDetalle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

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

        return view('Rector.reportes.boletines.index', [
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

        return view('Rector.reportes.boletines.show', [
            'currentPage' => 'Boletines',
            'boletin' => $boletin,
        ]);
    }

    public function pdf(Boletin $boletin): \Illuminate\Http\Response
    {
        $boletin->load(['estudiante.usuario', 'periodo', 'detalle.asignacion.materia', 'detalle.asignacion.profesor.usuario']);

        $curso = Matricula::where('id_estudiante', $boletin->id_estudiante)
            ->where('estado_matricula', 'activa')
            ->with('curso')
            ->first()?->curso;

        $pdf = Pdf::loadView('Rector.reportes.boletines.pdf', [
            'boletin' => $boletin,
            'curso' => $curso,
            'colegio' => ColegioConfiguracion::query()->find(1),
        ]);

        return $pdf->download(self::nombreArchivoPdf($boletin->estudiante->codigo_estudiante, $boletin->periodo->nombre_periodo));
    }

    public function pdfMasivo(Request $request): BinaryFileResponse
    {
        $data = $request->validate([
            'id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'id_periodo' => ['required', 'integer', 'exists:periodos_academicos,id_periodo'],
        ]);

        $curso = Curso::findOrFail($data['id_curso']);
        $idPeriodo = (int) $data['id_periodo'];

        $boletines = Boletin::where('id_periodo', $idPeriodo)
            ->whereHas('estudiante.matriculas', fn ($q) => $q->where('id_curso', $curso->id_curso)->where('estado_matricula', 'activa'))
            ->with(['estudiante.usuario', 'periodo', 'detalle.asignacion.materia', 'detalle.asignacion.profesor.usuario'])
            ->orderByRaw('puesto_curso IS NULL, puesto_curso')
            ->get();

        abort_if($boletines->isEmpty(), 404, 'No hay boletines generados para este curso y periodo.');

        $colegio = ColegioConfiguracion::query()->find(1);

        $zipPath = tempnam(sys_get_temp_dir(), 'boletines_');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::OVERWRITE);

        foreach ($boletines as $boletin) {
            $contenidoPdf = Pdf::loadView('Rector.reportes.boletines.pdf', [
                'boletin' => $boletin,
                'curso' => $curso,
                'colegio' => $colegio,
            ])->output();

            $zip->addFromString(self::nombreArchivoPdf($boletin->estudiante->codigo_estudiante, $boletin->periodo->nombre_periodo), $contenidoPdf);
        }

        $zip->close();

        $nombrePeriodo = $boletines->first()->periodo->nombre_periodo ?? $idPeriodo;
        $nombreZip = Str::slug("boletines-{$curso->nombre_curso}-{$nombrePeriodo}").'.zip';

        return response()->download($zipPath, $nombreZip)->deleteFileAfterSend(true);
    }

    private static function nombreArchivoPdf(string $codigo, string $periodo): string
    {
        return Str::slug("boletin-{$codigo}-{$periodo}").'.pdf';
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
