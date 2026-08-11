<?php

namespace App\Modules\Reportes\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calificaciones\Models\EscalaNota;
use App\Modules\Calificaciones\Models\Periodo;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Matriculas\Models\Matricula;
use App\Modules\Reportes\Models\Boletin;
use App\Modules\Reportes\Models\BoletinDetalle;
use App\Modules\Reportes\Support\BoletinCalculador;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class BoletinesController extends Controller
{
    public function __construct(private readonly BoletinCalculador $calculador) {}

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

        foreach ($idsEstudiantes as $idEstudiante) {
            $boletin = Boletin::firstOrCreate(
                ['id_estudiante' => $idEstudiante, 'id_periodo' => $idPeriodo],
                ['estado' => 'borrador']
            );

            foreach ($asignaciones as $asignacion) {
                $detalleExistente = BoletinDetalle::where('id_boletin', $boletin->id_boletin)
                    ->where('id_asignacion', $asignacion->id_asignacion)
                    ->first();

                // Una nota digitada manualmente por el director de grupo no se
                // recalcula aquí — se conserva tal cual hasta que el propio
                // director la borre (ver DirectorGrupoController::guardar).
                if ($detalleExistente?->origen === BoletinDetalle::ORIGEN_MANUAL) {
                    continue;
                }

                $notaDefinitiva = $this->calculador->calcularNotaCalculada($asignacion, $idPeriodo, $idEstudiante);

                BoletinDetalle::updateOrCreate(
                    ['id_boletin' => $boletin->id_boletin, 'id_asignacion' => $asignacion->id_asignacion],
                    ['nota_definitiva' => $notaDefinitiva, 'origen' => BoletinDetalle::ORIGEN_CALCULADO]
                );
            }
        }

        $this->calculador->recalcularResumenCurso($curso, $idPeriodo);

        return response()->json(['success' => true, 'message' => 'Boletines generados correctamente.']);
    }

    public function show(Boletin $boletin): View
    {
        $boletin->load(['estudiante.usuario', 'periodo', 'detalle.asignacion.materia']);

        return view('Rector.reportes.boletines.show', [
            'currentPage' => 'Boletines',
            'boletin' => $boletin,
            'escalas' => EscalaNota::orderBy('orden')->get(),
        ]);
    }

    public function pdf(Boletin $boletin): \Illuminate\Http\Response
    {
        $boletin->load(['estudiante.usuario', 'periodo', 'detalle.asignacion.materia']);

        $curso = Matricula::where('id_estudiante', $boletin->id_estudiante)
            ->where('estado_matricula', 'activa')
            ->with('curso.director.usuario')
            ->first()?->curso;

        $colegio = ColegioConfiguracion::query()->find(1);

        $pdf = Pdf::loadView('Rector.reportes.boletines.pdf', [
            'boletin' => $boletin,
            'curso' => $curso,
            'colegio' => $colegio,
            'logoBase64' => $this->logoBase64($colegio),
            'escalas' => EscalaNota::orderBy('orden')->get(),
        ]);

        return $pdf->download(self::nombreArchivoPdf($boletin->estudiante->codigo_estudiante, $boletin->periodo->nombre_periodo));
    }

    public function pdfMasivo(Request $request): BinaryFileResponse
    {
        $data = $request->validate([
            'id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'id_periodo' => ['required', 'integer', 'exists:periodos_academicos,id_periodo'],
        ]);

        $curso = Curso::with('director.usuario')->findOrFail($data['id_curso']);
        $idPeriodo = (int) $data['id_periodo'];

        $boletines = Boletin::where('id_periodo', $idPeriodo)
            ->whereHas('estudiante.matriculas', fn ($q) => $q->where('id_curso', $curso->id_curso)->where('estado_matricula', 'activa'))
            ->with(['estudiante.usuario', 'periodo', 'detalle.asignacion.materia'])
            ->orderByRaw('puesto_curso IS NULL, puesto_curso')
            ->get();

        abort_if($boletines->isEmpty(), 404, 'No hay boletines generados para este curso y periodo.');

        $colegio = ColegioConfiguracion::query()->find(1);
        $logoBase64 = $this->logoBase64($colegio);
        $escalas = EscalaNota::orderBy('orden')->get();

        $zipPath = tempnam(sys_get_temp_dir(), 'boletines_');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::OVERWRITE);

        foreach ($boletines as $boletin) {
            $contenidoPdf = Pdf::loadView('Rector.reportes.boletines.pdf', [
                'boletin' => $boletin,
                'curso' => $curso,
                'colegio' => $colegio,
                'logoBase64' => $logoBase64,
                'escalas' => $escalas,
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

    /** Data URI del logo para incrustarlo en el PDF — DomPDF no tiene habilitado enable_remote,
     *  así que una URL de Storage::disk('public')->url() no se renderiza de forma confiable. */
    private function logoBase64(?ColegioConfiguracion $colegio): ?string
    {
        if (! $colegio?->logo || ! Storage::disk('public')->exists($colegio->logo)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($colegio->logo);
        $contenido = Storage::disk('public')->get($colegio->logo);

        return "data:{$mime};base64,".base64_encode($contenido);
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
