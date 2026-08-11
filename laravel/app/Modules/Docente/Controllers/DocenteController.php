<?php

namespace App\Modules\Docente\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Asistencia\Models\Asistencia;
use App\Modules\Calificaciones\Models\Nota;
use App\Modules\Comunicados\Models\Notificacion;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\GestionAcademica\Models\Horario;
use App\Modules\Matriculas\Models\Matricula;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteController extends Controller
{
    public function dashboard(): View
    {
        $profesor = $this->profesorAutenticado();
        $asignaciones = $this->misAsignaciones($profesor);

        $idsAsignaciones = $asignaciones->pluck('id_asignacion');
        $idsCursos = $asignaciones->pluck('id_curso')->unique();

        $asignaciones = $asignaciones->map(function ($asignacion) {
            $ids = collect([$asignacion->id_asignacion]);
            $asignacion->en_riesgo = $this->estudiantesEnRiesgo($ids)->count();

            return $asignacion;
        });

        $horarios = Horario::whereHas('asignacion', fn ($q) => $q->where('id_profesor', $profesor->id_profesor)->where('estado', 'activo'))
            ->where('estado', 'activo')
            ->with(['asignacion.materia', 'asignacion.curso'])
            ->get();

        return view('Docente.dashboard', [
            'currentPage' => 'DocenteDashboard',
            'asignaciones' => $asignaciones,
            'proximaClase' => $this->proximaClase($horarios),
            'comunicadosRecientes' => Notificacion::where('id_usuario_destino', auth()->id())
                ->orderByDesc('fecha_envio')
                ->limit(4)
                ->get(),
            'resumen' => [
                'grupos' => $asignaciones->count(),
                'estudiantes' => Matricula::where('estado_matricula', 'activa')
                    ->whereIn('id_curso', $idsCursos)
                    ->distinct('id_estudiante')
                    ->count('id_estudiante'),
                'promedio' => round((float) Nota::whereHas('actividad', fn ($q) => $q->whereIn('id_asignacion', $idsAsignaciones))
                    ->avg('nota'), 1),
                'asistencia' => round((float) Asistencia::whereIn('id_asignacion', $idsAsignaciones)
                    ->selectRaw('SUM(estado_asistencia = "presente") / COUNT(*) * 100 as pct')
                    ->value('pct'), 1),
                'en_riesgo' => $this->estudiantesEnRiesgo($idsAsignaciones)->count(),
            ],
        ]);
    }

    public function horario(): View
    {
        $profesor = $this->profesorAutenticado();

        $horarios = Horario::whereHas('asignacion', fn ($q) => $q->where('id_profesor', $profesor->id_profesor)->where('estado', 'activo'))
            ->where('estado', 'activo')
            ->with(['asignacion.materia', 'asignacion.curso', 'asignacion.profesor.usuario'])
            ->get();

        return view('Docente.horario', [
            'currentPage' => 'DocenteHorario',
            'horarios' => $horarios,
        ]);
    }

    public function asistencia(): View
    {
        $profesor = $this->profesorAutenticado();

        return view('Docente.asistencia', [
            'currentPage' => 'DocenteAsistencia',
            'asignaciones' => $this->misAsignaciones($profesor),
        ]);
    }

    public function calificaciones(): View
    {
        $profesor = $this->profesorAutenticado();

        return view('Docente.calificaciones', [
            'currentPage' => 'DocenteCalificaciones',
            'asignaciones' => $this->misAsignaciones($profesor),
        ]);
    }

    public function estudiantes(): View
    {
        $profesor = $this->profesorAutenticado();
        $asignaciones = $this->misAsignaciones($profesor);

        $grupos = $asignaciones->map(function ($asignacion) {
            $estudiantes = Matricula::where('estado_matricula', 'activa')
                ->where('id_curso', $asignacion->id_curso)
                ->with('estudiante.usuario')
                ->get()
                ->filter(fn ($matricula) => $matricula->estudiante !== null)
                ->map(function ($matricula) use ($asignacion) {
                    $estudiante = $matricula->estudiante;

                    $promedio = Nota::whereHas('actividad', fn ($q) => $q->where('id_asignacion', $asignacion->id_asignacion))
                        ->where('id_estudiante', $estudiante->id_estudiante)
                        ->avg('nota');

                    $asistenciaPct = Asistencia::where('id_asignacion', $asignacion->id_asignacion)
                        ->where('id_estudiante', $estudiante->id_estudiante)
                        ->selectRaw('SUM(estado_asistencia = "presente") / COUNT(*) * 100 as pct')
                        ->value('pct');

                    return (object) [
                        'estudiante' => $estudiante,
                        'promedio' => $promedio !== null ? round((float) $promedio, 1) : null,
                        'asistencia' => $asistenciaPct !== null ? round((float) $asistenciaPct, 1) : null,
                        'en_riesgo' => ($promedio !== null && $promedio < 3.5) || ($asistenciaPct !== null && $asistenciaPct < 80),
                    ];
                })
                ->sortBy(fn ($fila) => $fila->estudiante->usuario->apellidos.$fila->estudiante->usuario->nombres)
                ->values();

            return (object) ['asignacion' => $asignacion, 'estudiantes' => $estudiantes];
        });

        return view('Docente.estudiantes', [
            'currentPage' => 'DocenteEstudiantes',
            'grupos' => $grupos,
        ]);
    }

    public function comunicados(): View
    {
        $comunicados = Notificacion::where('id_usuario_destino', auth()->id())
            ->orderByDesc('fecha_envio')
            ->paginate(15);

        return view('Docente.comunicados', [
            'currentPage' => 'DocenteComunicados',
            'comunicados' => $comunicados,
        ]);
    }

    public function marcarComunicadoLeido(Request $request, Notificacion $notificacion): \Illuminate\Http\JsonResponse
    {
        abort_unless($notificacion->id_usuario_destino === auth()->id(), 403);

        $notificacion->update(['leida' => true, 'fecha_lectura' => now()]);

        return response()->json(['success' => true]);
    }

    private function profesorAutenticado(): Profesor
    {
        $profesor = auth()->user()->profesor;

        abort_if(! $profesor, 404, 'Tu usuario no tiene un perfil de profesor asociado. Contacta al colegio.');

        return $profesor;
    }

    /** Primer bloque de horario que sigue: hoy si todavía falta alguno, si no el próximo día
     *  con clase recorriendo el ciclo semanal (lunes..sábado) desde mañana. Null sin horario. */
    private function proximaClase(\Illuminate\Support\Collection $horarios): ?Horario
    {
        if ($horarios->isEmpty()) {
            return null;
        }

        $dias = Horario::DIAS_SEMANA;
        $indiceHoy = match (now()->dayOfWeek) {
            1, 2, 3, 4, 5, 6 => now()->dayOfWeek - 1,
            default => null,
        };

        if ($indiceHoy !== null) {
            $horaActual = now()->format('H:i:s');
            $siguienteHoy = $horarios->where('dia_semana', $dias[$indiceHoy])
                ->filter(fn ($h) => $h->hora_inicio > $horaActual)
                ->sortBy('hora_inicio')
                ->first();

            if ($siguienteHoy) {
                return $siguienteHoy;
            }
        }

        $inicio = $indiceHoy !== null ? $indiceHoy + 1 : 0;
        for ($i = 0; $i < count($dias); $i++) {
            $dia = $dias[($inicio + $i) % count($dias)];
            $clase = $horarios->where('dia_semana', $dia)->sortBy('hora_inicio')->first();
            if ($clase) {
                return $clase;
            }
        }

        return null;
    }

    private function misAsignaciones(Profesor $profesor): \Illuminate\Support\Collection
    {
        return AsignacionAcademica::where('id_profesor', $profesor->id_profesor)
            ->where('estado', 'activo')
            ->with(['materia', 'curso'])
            ->get()
            ->sortBy(fn ($asignacion) => $asignacion->curso->nombre_curso.$asignacion->materia->nombre_materia)
            ->values();
    }

    /** Mismo criterio que Estudiante::idsEnRiesgo() (promedio < 3.5 o asistencia < 80%), pero acotado a las
     *  asignaciones de este docente en vez de todos los boletines de la institución. */
    private function estudiantesEnRiesgo($idsAsignaciones): \Illuminate\Support\Collection
    {
        $porPromedio = Nota::whereHas('actividad', fn ($q) => $q->whereIn('id_asignacion', $idsAsignaciones))
            ->groupBy('id_estudiante')
            ->havingRaw('AVG(nota) < 3.5')
            ->pluck('id_estudiante');

        $porAsistencia = Asistencia::whereIn('id_asignacion', $idsAsignaciones)
            ->groupBy('id_estudiante')
            ->havingRaw('SUM(estado_asistencia = "presente") / COUNT(*) < 0.8')
            ->pluck('id_estudiante');

        return $porPromedio->merge($porAsistencia)->unique();
    }
}
