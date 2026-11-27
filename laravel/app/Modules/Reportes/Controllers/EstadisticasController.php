<?php

namespace App\Modules\Reportes\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Asistencia\Models\Asistencia;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Instituciones\Support\TenantContext;
use App\Modules\Reportes\Models\Boletin;
use App\Modules\Reportes\Models\BoletinDetalle;
use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Estadísticas institucionales. Toda cifra se limita a la institución de la
 * sesión; sin institución (p. ej. SuperAdmin) todas las cifras salen en cero.
 */
class EstadisticasController extends Controller
{
    public function index(): View
    {
        $idInstitucion = TenantContext::idActual();

        $asistenciaPromedio = Asistencia::deInstitucion()->selectRaw('SUM(estado_asistencia = "presente") / COUNT(*) * 100 as pct')->value('pct');

        return view('Rector.reportes.estadisticas.index', [
            'currentPage' => 'Estadisticas',
            'resumen' => [
                'estudiantes' => Estudiante::deInstitucion()->where('estado_academico', 'activo')->count(),
                'docentes' => Profesor::deInstitucion()->where('estado_laboral', 'activo')->count(),
                'cursos' => Curso::deInstitucion()->where('estado', 'activo')->count(),
                'en_riesgo' => Estudiante::idsEnRiesgo()->count(),
            ],
            'promedioInstitucional' => round((float) Boletin::deInstitucion()->where('estado', 'publicado')->avg('promedio_general'), 2),
            'asistenciaPromedio' => round((float) $asistenciaPromedio, 1),
            'distribucionNotas' => $this->distribucionNotas($idInstitucion),
            'rankingCursos' => $this->rankingCursos($idInstitucion),
        ]);
    }

    /**
     * Fase 1 (P1-3): antes contaba boletin_detalle de TODAS las instituciones.
     * Un detalle cuenta solo si las dos cadenas que lo atan a un colegio
     * coinciden con la sesión:
     *  - boletín → estudiante → usuario.id_institucion (scope deInstitucion);
     *  - asignación → curso.id_institucion (un dato cruzado no se cuela).
     * whereHas genera EXISTS (sin joins, sin duplicados) y una sola consulta
     * agregada calcula los cuatro rangos. Notas NULL no cuentan.
     *
     * @return array{bajo: int, basico: int, alto: int, superior: int}
     */
    private function distribucionNotas(?int $idInstitucion): array
    {
        $fila = BoletinDetalle::query()
            ->deInstitucion($idInstitucion)
            ->whereHas('asignacion.curso', fn ($q) => $q->where('cursos.id_institucion', $idInstitucion ?? 0))
            ->toBase()
            ->selectRaw('SUM(CASE WHEN nota_definitiva < 3.0 THEN 1 ELSE 0 END) AS bajo')
            ->selectRaw('SUM(CASE WHEN nota_definitiva >= 3.0 AND nota_definitiva <= 3.99 THEN 1 ELSE 0 END) AS basico')
            ->selectRaw('SUM(CASE WHEN nota_definitiva >= 4.0 AND nota_definitiva <= 4.49 THEN 1 ELSE 0 END) AS alto')
            ->selectRaw('SUM(CASE WHEN nota_definitiva >= 4.5 THEN 1 ELSE 0 END) AS superior')
            ->first();

        return [
            'bajo' => (int) ($fila->bajo ?? 0),
            'basico' => (int) ($fila->basico ?? 0),
            'alto' => (int) ($fila->alto ?? 0),
            'superior' => (int) ($fila->superior ?? 0),
        ];
    }

    /**
     * Promedio de boletines publicados por curso. La matrícula se toma del MISMO
     * año lectivo que el periodo del boletín: con la única (id_estudiante,
     * anio_lectivo) de matriculas hay a lo sumo una fila por boletín, así que el
     * join no duplica (antes, una matrícula activa de otro año contaba dos veces).
     * El curso y el estudiante deben ser de la institución de la sesión.
     */
    private function rankingCursos(?int $idInstitucion)
    {
        $id = $idInstitucion ?? 0;

        return DB::table('boletines')
            ->join('periodos_academicos', 'periodos_academicos.id_periodo', '=', 'boletines.id_periodo')
            ->join('matriculas', function ($join) {
                $join->on('matriculas.id_estudiante', '=', 'boletines.id_estudiante')
                    ->on('matriculas.anio_lectivo', '=', 'periodos_academicos.anio_lectivo')
                    ->where('matriculas.estado_matricula', '=', 'activa');
            })
            ->join('cursos', 'cursos.id_curso', '=', 'matriculas.id_curso')
            ->join('estudiantes', 'estudiantes.id_estudiante', '=', 'boletines.id_estudiante')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'estudiantes.id_usuario')
            ->where('cursos.id_institucion', $id)
            ->where('usuarios.id_institucion', $id)
            ->where('boletines.estado', 'publicado')
            ->whereNotNull('boletines.promedio_general')
            ->groupBy('cursos.id_curso', 'cursos.nombre_curso')
            ->selectRaw('cursos.nombre_curso, AVG(boletines.promedio_general) as promedio, COUNT(DISTINCT boletines.id_boletin) as total_boletines')
            ->orderByDesc('promedio')
            ->get();
    }
}
