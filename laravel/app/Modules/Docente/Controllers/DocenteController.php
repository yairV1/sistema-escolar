<?php

namespace App\Modules\Docente\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use Illuminate\View\View;

class DocenteController extends Controller
{
    public function dashboard(): View
    {
        $profesor = auth()->user()->profesor;

        abort_if(! $profesor, 404, 'Tu usuario no tiene un perfil de profesor asociado. Contacta al colegio.');

        $asignaciones = AsignacionAcademica::where('id_profesor', $profesor->id_profesor)
            ->where('estado', 'activo')
            ->with(['materia', 'curso.matriculas'])
            ->get()
            ->sortBy(fn ($asignacion) => $asignacion->curso->nombre_curso.$asignacion->materia->nombre_materia);

        $asignaciones->each(function ($asignacion) {
            $asignacion->estudiantesActivos = $asignacion->curso->matriculas
                ->where('estado_matricula', 'activa')
                ->count();
        });

        $kpis = [
            'cursos' => $asignaciones->pluck('id_curso')->unique()->count(),
            'materias' => $asignaciones->pluck('id_materia')->unique()->count(),
            'estudiantes' => $asignaciones->pluck('id_curso')->unique()
                ->sum(fn ($idCurso) => $asignaciones->firstWhere('id_curso', $idCurso)->estudiantesActivos),
        ];

        return view('Docente.dashboard', [
            'currentPage' => 'DocenteDashboard',
            'asignaciones' => $asignaciones,
            'kpis' => $kpis,
        ]);
    }
}
