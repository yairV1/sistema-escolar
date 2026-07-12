<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $anioActual = now()->year;

        $estudiantesMatriculados = Matricula::where('anio_lectivo', $anioActual)
            ->where('estado_matricula', 'activa')
            ->count();

        $docentesActivos = Profesor::where('estado_laboral', 'activo')->count();

        $solicitudesPendientes = Matricula::where('estado_matricula', 'pendiente')->count();

        $promedioInstitucional = DB::table('boletines')->avg('promedio_general');

        $asistenciaPromedio = DB::table('asistencia')
            ->selectRaw('SUM(estado_asistencia = "presente") / NULLIF(COUNT(*), 0) * 100 as porcentaje')
            ->value('porcentaje');

        $estudiantesEnRiesgo = Estudiante::idsEnRiesgo()->count();

        $promedioPorGrado = DB::table('cursos')
            ->join('matriculas', 'matriculas.id_curso', '=', 'cursos.id_curso')
            ->join('boletines', 'boletines.id_estudiante', '=', 'matriculas.id_estudiante')
            ->select('cursos.nombre_curso', 'cursos.nivel_academico', DB::raw('AVG(boletines.promedio_general) as promedio'))
            ->groupBy('cursos.nombre_curso', 'cursos.nivel_academico')
            ->orderByRaw("FIELD(cursos.nivel_academico, 'preescolar','primaria','secundaria','media')")
            ->orderBy('cursos.nombre_curso')
            ->get();

        $matriculasRecientes = Matricula::with(['estudiante.usuario', 'curso'])
            ->orderByDesc('fecha_matricula')
            ->limit(5)
            ->get();

        $docentesLista = Profesor::with(['usuario', 'asignaciones.materia', 'asignaciones.curso'])
            ->where('estado_laboral', 'activo')
            ->limit(6)
            ->get();

        $estudiantesMuestra = Estudiante::with(['usuario', 'matriculas' => fn ($q) => $q->latest('fecha_matricula')->with('curso')])
            ->where('estado_academico', 'activo')
            ->limit(6)
            ->get();

        return view('dashboard.index', [
            'currentPage' => 'inicio',
            'kpis' => [
                'estudiantesMatriculados' => $estudiantesMatriculados,
                'docentesActivos' => $docentesActivos,
                'solicitudesPendientes' => $solicitudesPendientes,
                'promedioInstitucional' => $promedioInstitucional,
                'asistenciaPromedio' => $asistenciaPromedio,
                'estudiantesEnRiesgo' => $estudiantesEnRiesgo,
            ],
            'promedioPorGrado' => $promedioPorGrado,
            'matriculasRecientes' => $matriculasRecientes,
            'docentesLista' => $docentesLista,
            'estudiantesMuestra' => $estudiantesMuestra,
        ]);
    }
}
