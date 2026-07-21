<?php

namespace App\Modules\Reportes\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Asistencia\Models\Asistencia;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Reportes\Models\Boletin;
use App\Modules\Reportes\Models\BoletinDetalle;
use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EstadisticasController extends Controller
{
    public function index(): View
    {
        $asistenciaPromedio = Asistencia::selectRaw('SUM(estado_asistencia = "presente") / COUNT(*) * 100 as pct')->value('pct');

        $rankingCursos = DB::table('boletines')
            ->join('estudiantes', 'boletines.id_estudiante', '=', 'estudiantes.id_estudiante')
            ->join('matriculas', function ($join) {
                $join->on('matriculas.id_estudiante', '=', 'estudiantes.id_estudiante')
                    ->where('matriculas.estado_matricula', '=', 'activa');
            })
            ->join('cursos', 'matriculas.id_curso', '=', 'cursos.id_curso')
            ->where('boletines.estado', 'publicado')
            ->whereNotNull('boletines.promedio_general')
            ->groupBy('cursos.id_curso', 'cursos.nombre_curso')
            ->selectRaw('cursos.nombre_curso, AVG(boletines.promedio_general) as promedio, COUNT(*) as total_boletines')
            ->orderByDesc('promedio')
            ->get();

        return view('Rector.reportes.estadisticas.index', [
            'currentPage' => 'Estadisticas',
            'resumen' => [
                'estudiantes' => Estudiante::where('estado_academico', 'activo')->count(),
                'docentes' => Profesor::where('estado_laboral', 'activo')->count(),
                'cursos' => Curso::where('estado', 'activo')->count(),
                'en_riesgo' => Estudiante::idsEnRiesgo()->count(),
            ],
            'promedioInstitucional' => round((float) Boletin::where('estado', 'publicado')->avg('promedio_general'), 2),
            'asistenciaPromedio' => round((float) $asistenciaPromedio, 1),
            'distribucionNotas' => [
                'bajo' => BoletinDetalle::where('nota_definitiva', '<', 3.0)->count(),
                'basico' => BoletinDetalle::whereBetween('nota_definitiva', [3.0, 3.99])->count(),
                'alto' => BoletinDetalle::whereBetween('nota_definitiva', [4.0, 4.49])->count(),
                'superior' => BoletinDetalle::where('nota_definitiva', '>=', 4.5)->count(),
            ],
            'rankingCursos' => $rankingCursos,
        ]);
    }
}
