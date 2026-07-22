<?php

namespace App\Modules\Matriculas\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Matriculas\Models\Matricula;
use App\Modules\Matriculas\Models\SolicitudAdmision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatriculasController extends Controller
{
    public function index(Request $request): View
    {
        $query = Matricula::query()->with(['estudiante.usuario', 'curso']);

        if ($q = $request->query('q')) {
            $query->where(function ($w) use ($q) {
                $w->whereHas('estudiante', function ($eq) use ($q) {
                    $eq->where('codigo_estudiante', 'like', "%{$q}%")
                        ->orWhereHas('usuario', function ($uq) use ($q) {
                            $uq->where('nombres', 'like', "%{$q}%")
                                ->orWhere('apellidos', 'like', "%{$q}%");
                        });
                });
            });
        }

        if ($curso = $request->query('curso')) {
            $query->where('id_curso', $curso);
        }

        if ($anio = $request->query('anio')) {
            $query->where('anio_lectivo', $anio);
        }

        $estado = $request->query('estado');
        if (in_array($estado, Matricula::ESTADOS, true)) {
            $query->where('estado_matricula', $estado);
        }

        $matriculas = $query->orderByDesc('fecha_matricula')->paginate(15)->withQueryString();

        return view('Rector.matriculas.index', [
            'currentPage' => 'Matriculas',
            'filtros' => $request->only(['q', 'estado', 'curso', 'anio']),
            'cursos' => Curso::orderBy('nivel_academico')->orderBy('nombre_curso')->get(),
            'anios' => Matricula::query()->distinct()->orderByDesc('anio_lectivo')->pluck('anio_lectivo'),
            'matriculas' => $matriculas,
            'solicitudes' => SolicitudAdmision::whereIn('estado', ['pendiente', 'contactada'])
                ->orderByDesc('created_at')
                ->get(),
            'resumen' => [
                'total' => Matricula::count(),
                'activas' => Matricula::where('estado_matricula', 'activa')->count(),
                'pendientes' => Matricula::where('estado_matricula', 'pendiente')->count(),
                'retiradasCanceladas' => Matricula::whereIn('estado_matricula', ['retirada', 'cancelada'])->count(),
            ],
        ]);
    }

    public function cambiarEstado(Request $request, Matricula $matricula): JsonResponse
    {
        $validated = $request->validate([
            'estado' => 'required|in:'.implode(',', Matricula::ESTADOS),
        ]);

        $matricula->update(['estado_matricula' => $validated['estado']]);

        return response()->json([
            'success' => true,
            'message' => 'El estado de la matrícula se actualizó correctamente.',
        ]);
    }

    public function cambiarEstadoSolicitud(Request $request, SolicitudAdmision $solicitud): JsonResponse
    {
        $validated = $request->validate([
            'estado' => 'required|in:contactada,descartada',
        ]);

        $solicitud->update(['estado' => $validated['estado']]);

        return response()->json([
            'success' => true,
            'message' => 'La solicitud se actualizó correctamente.',
        ]);
    }
}
