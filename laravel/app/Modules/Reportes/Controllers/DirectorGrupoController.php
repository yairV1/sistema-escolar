<?php

namespace App\Modules\Reportes\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calificaciones\Models\Periodo;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Reportes\Models\Boletin;
use App\Modules\Reportes\Models\BoletinDetalle;
use App\Modules\Reportes\Support\BoletinCalculador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pantalla del director de grupo (docente asignado como Curso::id_director_grupo)
 * para digitar la nota definitiva de cada materia directamente, sin pasar
 * actividad por actividad, una vez cerrado el periodo. Ver BoletinCalculador y
 * BoletinesController::generar() para cómo conviven las notas manuales con la
 * regeneración masiva del rector.
 */
class DirectorGrupoController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = auth()->user();
        $profesor = $usuario->profesor;

        $cursos = $profesor && ! $usuario->tienePanelAdmin()
            ? Curso::where('id_director_grupo', $profesor->id_profesor)->where('estado', 'activo')->orderBy('nombre_curso')->get()
            : Curso::where('estado', 'activo')->orderBy('nombre_curso')->get();

        $idCurso = $request->query('curso');
        $idPeriodo = $request->query('periodo');

        return view('Docente.director-grupo.index', [
            'currentPage' => 'DocenteDirectorGrupo',
            'esDirectorDeAlgunCurso' => $profesor && Curso::where('id_director_grupo', $profesor->id_profesor)->exists(),
            'cursos' => $cursos,
            'periodos' => Periodo::orderByDesc('anio_lectivo')->orderBy('fecha_inicio')->get(),
            'idCurso' => $idCurso,
            'idPeriodo' => $idPeriodo,
        ]);
    }

    public function notas(Curso $curso, Periodo $periodo): View
    {
        $this->autorizar($curso);

        $asignaciones = $curso->asignaciones()->where('estado', 'activo')->with('materia')->get()
            ->sortBy(fn ($a) => $a->materia->nombre_materia ?? '')
            ->values();

        $estudiantes = $curso->matriculas()->where('estado_matricula', 'activa')->with('estudiante.usuario')->get()
            ->pluck('estudiante')
            ->filter()
            ->sortBy(fn ($e) => $e->usuario->apellidos.$e->usuario->nombres)
            ->values();

        $idsBoletines = Boletin::where('id_periodo', $periodo->id_periodo)
            ->whereIn('id_estudiante', $estudiantes->pluck('id_estudiante'))
            ->pluck('id_boletin', 'id_estudiante');

        $detalles = BoletinDetalle::whereIn('id_boletin', $idsBoletines->values())
            ->whereIn('id_asignacion', $asignaciones->pluck('id_asignacion'))
            ->get();

        $notasExistentes = [];
        foreach ($idsBoletines as $idEstudiante => $idBoletin) {
            foreach ($detalles->where('id_boletin', $idBoletin) as $detalle) {
                $notasExistentes[$idEstudiante][$detalle->id_asignacion] = $detalle;
            }
        }

        return view('Docente.director-grupo.notas', [
            'currentPage' => 'DocenteDirectorGrupo',
            'curso' => $curso,
            'periodo' => $periodo,
            'asignaciones' => $asignaciones,
            'estudiantes' => $estudiantes,
            'notasExistentes' => $notasExistentes,
        ]);
    }

    public function guardar(Request $request, Curso $curso, Periodo $periodo, BoletinCalculador $calculador): JsonResponse
    {
        $this->autorizar($curso);

        $data = $request->validate([
            'notas' => ['required', 'array'],
            'notas.*.id_estudiante' => ['required', 'integer', 'exists:estudiantes,id_estudiante'],
            'notas.*.id_asignacion' => ['required', 'integer', 'exists:asignaciones_academicas,id_asignacion'],
            'notas.*.nota' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ]);

        $asignaciones = $curso->asignaciones()->where('estado', 'activo')->get()->keyBy('id_asignacion');

        foreach ($data['notas'] as $fila) {
            $asignacion = $asignaciones->get($fila['id_asignacion']);

            if (! $asignacion) {
                continue;
            }

            $boletin = Boletin::firstOrCreate(
                ['id_estudiante' => $fila['id_estudiante'], 'id_periodo' => $periodo->id_periodo],
                ['estado' => 'borrador']
            );

            $detalleExistente = BoletinDetalle::where('id_boletin', $boletin->id_boletin)
                ->where('id_asignacion', $asignacion->id_asignacion)
                ->first();

            $valor = $fila['nota'] ?? null;

            if ($valor !== null) {
                BoletinDetalle::updateOrCreate(
                    ['id_boletin' => $boletin->id_boletin, 'id_asignacion' => $asignacion->id_asignacion],
                    ['nota_definitiva' => $valor, 'origen' => BoletinDetalle::ORIGEN_MANUAL]
                );
            } elseif ($detalleExistente?->origen === BoletinDetalle::ORIGEN_MANUAL) {
                // Celda vaciada: se "deshace" la nota manual y vuelve a quedar en manos del cálculo automático.
                $notaCalculada = $calculador->calcularNotaCalculada($asignacion, $periodo->id_periodo, $fila['id_estudiante']);
                $detalleExistente->update(['nota_definitiva' => $notaCalculada, 'origen' => BoletinDetalle::ORIGEN_CALCULADO]);
            }
        }

        $calculador->recalcularResumenCurso($curso, $periodo->id_periodo);

        return response()->json(['success' => true, 'message' => 'Notas definitivas guardadas correctamente.']);
    }

    private function autorizar(Curso $curso): void
    {
        abort_unless(auth()->user()->esDirectorDeGrupo($curso) || auth()->user()->tienePanelAdmin(), 403);
    }
}
