<?php

namespace App\Modules\Reportes\Support;

use App\Modules\Calificaciones\Models\Nota;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Reportes\Models\Boletin;
use App\Modules\Reportes\Models\BoletinDetalle;

/**
 * Cálculo compartido entre la generación masiva de boletines
 * (BoletinesController::generar) y la edición manual del director de grupo
 * (DirectorGrupoController): promedio ponderado por actividad, y el resumen
 * del curso (promedio general + puesto) a partir de las notas definitivas ya
 * guardadas, sin importar si su origen fue calculado o manual.
 */
class BoletinCalculador
{
    public function calcularNotaCalculada(AsignacionAcademica $asignacion, int $idPeriodo, int $idEstudiante): ?float
    {
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

        return $sumaPorcentajes > 0 ? round($sumaPonderada / $sumaPorcentajes, 2) : null;
    }

    /**
     * Recalcula promedio_general y re-rankea puesto_curso de todos los boletines del
     * curso en ese periodo, leyendo nota_definitiva tal cual quedó guardada (calculada
     * o manual). Solo considera asignaciones activas del curso, igual que antes de
     * extraer este método.
     */
    public function recalcularResumenCurso(Curso $curso, int $idPeriodo): void
    {
        $idsEstudiantes = $curso->matriculas()->where('estado_matricula', 'activa')->pluck('id_estudiante');

        $promedios = [];

        foreach ($idsEstudiantes as $idEstudiante) {
            $boletin = Boletin::where('id_estudiante', $idEstudiante)->where('id_periodo', $idPeriodo)->first();

            if (! $boletin) {
                continue;
            }

            $notasMaterias = BoletinDetalle::where('id_boletin', $boletin->id_boletin)
                ->whereHas('asignacion', fn ($q) => $q->where('id_curso', $curso->id_curso)->where('estado', 'activo'))
                ->whereNotNull('nota_definitiva')
                ->pluck('nota_definitiva');

            $promedioGeneral = $notasMaterias->count() > 0 ? round((float) $notasMaterias->avg(), 2) : null;
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
    }
}
