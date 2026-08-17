<?php

namespace App\Modules\Estudiante\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Shared\Fixtures\PortalFamiliaFixtures;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * UI-only: el contenido académico viene de PortalFamiliaFixtures (datos de
 * muestra), pendiente de conectar a los módulos reales de Calificaciones/
 * Asistencia/Comunicados. El nombre y rol sí vienen del usuario autenticado.
 */
class EstudianteController extends Controller
{
    public function inicio(): View
    {
        $horario = PortalFamiliaFixtures::horario();
        $diaHoy = match (now()->dayOfWeek) {
            1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes',
            default => null,
        };
        $clasesHoy = $diaHoy ? $horario->where('dia_semana', $diaHoy)->sortBy('hora_inicio')->values() : collect();

        $periodoActual = PortalFamiliaFixtures::notasPorPeriodo()['Periodo 3'];
        $comunicados = PortalFamiliaFixtures::comunicados()->take(3);

        $tareasPendientes = collect([
            (object) ['titulo' => 'Taller de ecuaciones — cap. 4', 'materia' => 'Matemáticas', 'fecha_entrega' => now()->addDays(2)->format('d/m')],
            (object) ['titulo' => 'Ensayo: Revolución Industrial', 'materia' => 'Ciencias Sociales', 'fecha_entrega' => now()->addDays(4)->format('d/m')],
        ]);

        return view('Estudiante.inicio', [
            'currentPage' => 'EstudianteInicio',
            'clasesHoy' => $clasesHoy,
            'tareasPendientes' => $tareasPendientes,
            'promedioGeneral' => $periodoActual->promedio_general,
            'comunicados' => $comunicados,
        ]);
    }

    public function horario(): View
    {
        return view('Estudiante.horario', [
            'currentPage' => 'EstudianteHorario',
            'horarios' => PortalFamiliaFixtures::horario(),
        ]);
    }

    public function materias(): View
    {
        return view('Estudiante.materias', [
            'currentPage' => 'EstudianteMaterias',
            'materias' => PortalFamiliaFixtures::materias(),
        ]);
    }

    public function notas(Request $request): View
    {
        $periodos = PortalFamiliaFixtures::notasPorPeriodo();
        $periodoSeleccionado = $request->query('periodo', 'Periodo 3');
        if (! isset($periodos[$periodoSeleccionado])) {
            $periodoSeleccionado = array_key_last($periodos);
        }

        return view('Estudiante.notas', [
            'currentPage' => 'EstudianteNotas',
            'periodos' => array_keys($periodos),
            'periodoSeleccionado' => $periodoSeleccionado,
            'boletin' => $periodos[$periodoSeleccionado],
        ]);
    }

    public function asistencia(): View
    {
        return view('Estudiante.asistencia', [
            'currentPage' => 'EstudianteAsistencia',
            'resumen' => PortalFamiliaFixtures::asistencia(),
        ]);
    }

    public function comunicados(): View
    {
        return view('Estudiante.comunicados', [
            'currentPage' => 'EstudianteComunicados',
            'comunicados' => PortalFamiliaFixtures::comunicados(),
        ]);
    }
}
