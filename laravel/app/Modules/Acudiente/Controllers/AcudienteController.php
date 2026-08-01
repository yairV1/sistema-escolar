<?php

namespace App\Modules\Acudiente\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Shared\Fixtures\PortalFamiliaFixtures;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * UI-only: el listado de hijos y su información académica vienen de
 * PortalFamiliaFixtures (datos de muestra). El hijo "activo" se resuelve
 * por query string (?estudiante=) en cada request, sin sesión ni escritura
 * en BD — solo selección de lectura entre los hijos de muestra.
 */
class AcudienteController extends Controller
{
    private const HIJOS = [
        1 => ['id' => 1, 'nombres' => 'Sofía', 'apellidos' => 'Martínez Gómez', 'curso' => '6°A', 'codigo' => 'EST-2026-014', 'perfil' => 'ordenado'],
        2 => ['id' => 2, 'nombres' => 'Mateo', 'apellidos' => 'Martínez Gómez', 'curso' => '9°B', 'codigo' => 'EST-2026-071', 'perfil' => 'con_alertas'],
    ];

    private function hijos(): \Illuminate\Support\Collection
    {
        return collect(self::HIJOS)->map(fn ($h) => (object) $h);
    }

    private function hijoActivo(Request $request): object
    {
        $id = (int) $request->query('estudiante', 1);

        return (object) (self::HIJOS[$id] ?? self::HIJOS[1]);
    }

    public function inicio(): View
    {
        $hijos = $this->hijos()->map(function ($hijo) {
            $notas = PortalFamiliaFixtures::notasPorPeriodo($hijo->perfil)['Periodo 3'];
            $asistencia = PortalFamiliaFixtures::asistencia($hijo->perfil);
            $alertas = [];

            if ($notas->promedio_general < 3.5) {
                $alertas[] = 'Promedio bajo este periodo ('.$notas->promedio_general.')';
            }
            if ($asistencia->porcentaje < 85) {
                $alertas[] = 'Asistencia baja ('.$asistencia->porcentaje.'%)';
            }
            if (PortalFamiliaFixtures::comunicados($hijo->perfil)->where('leido', false)->isNotEmpty()) {
                $alertas[] = PortalFamiliaFixtures::comunicados($hijo->perfil)->where('leido', false)->count().' comunicado(s) nuevo(s)';
            }

            $hijo->promedio = $notas->promedio_general;
            $hijo->asistencia = $asistencia->porcentaje;
            $hijo->alertas = $alertas;

            return $hijo;
        });

        return view('Acudiente.inicio', [
            'currentPage' => 'AcudienteInicio',
            'hijos' => $hijos,
        ]);
    }

    public function estudiantes(): View
    {
        return view('Acudiente.estudiantes', [
            'currentPage' => 'AcudienteEstudiantes',
            'hijos' => $this->hijos(),
        ]);
    }

    public function horario(Request $request): View
    {
        $hijo = $this->hijoActivo($request);

        return view('Acudiente.horario', [
            'currentPage' => 'AcudienteHorario',
            'hijos' => $this->hijos(),
            'hijoActivo' => $hijo,
            'horarios' => PortalFamiliaFixtures::horario($hijo->curso),
        ]);
    }

    public function notas(Request $request): View
    {
        $hijo = $this->hijoActivo($request);
        $periodos = PortalFamiliaFixtures::notasPorPeriodo($hijo->perfil);
        $periodoSeleccionado = $request->query('periodo', 'Periodo 3');
        if (! isset($periodos[$periodoSeleccionado])) {
            $periodoSeleccionado = array_key_last($periodos);
        }

        return view('Acudiente.notas', [
            'currentPage' => 'AcudienteNotas',
            'hijos' => $this->hijos(),
            'hijoActivo' => $hijo,
            'periodos' => array_keys($periodos),
            'periodoSeleccionado' => $periodoSeleccionado,
            'boletin' => $periodos[$periodoSeleccionado],
        ]);
    }

    public function asistencia(Request $request): View
    {
        $hijo = $this->hijoActivo($request);

        return view('Acudiente.asistencia', [
            'currentPage' => 'AcudienteAsistencia',
            'hijos' => $this->hijos(),
            'hijoActivo' => $hijo,
            'resumen' => PortalFamiliaFixtures::asistencia($hijo->perfil),
        ]);
    }

    public function comunicados(Request $request): View
    {
        $hijo = $this->hijoActivo($request);

        return view('Acudiente.comunicados', [
            'currentPage' => 'AcudienteComunicados',
            'hijos' => $this->hijos(),
            'hijoActivo' => $hijo,
            'comunicados' => PortalFamiliaFixtures::comunicados($hijo->perfil),
        ]);
    }

    public function perfil(): View
    {
        return view('Acudiente.perfil', [
            'currentPage' => 'AcudientePerfil',
            'usuario' => auth()->user(),
            'hijos' => $this->hijos()->map(fn ($h) => (object) array_merge((array) $h, [
                'parentesco' => 'Madre',
                'es_principal' => $h->id === 1,
            ])),
        ]);
    }
}
