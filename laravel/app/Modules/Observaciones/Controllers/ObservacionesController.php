<?php

namespace App\Modules\Observaciones\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Observaciones\Models\Observacion;
use App\Modules\Observaciones\Requests\ObservacionRequest;
use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObservacionesController extends Controller
{
    public function index(Request $request): View
    {
        $query = Observacion::query()->with(['estudiante.usuario', 'profesor.usuario']);

        if ($q = $request->query('q')) {
            $query->whereHas('estudiante', function ($eq) use ($q) {
                $eq->where('codigo_estudiante', 'like', "%{$q}%")
                    ->orWhereHas('usuario', function ($uq) use ($q) {
                        $uq->where('nombres', 'like', "%{$q}%")
                            ->orWhere('apellidos', 'like', "%{$q}%");
                    });
            });
        }

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo_observacion', $tipo);
        }

        $estado = $request->query('estado');
        if (in_array($estado, ['activa', 'archivada'], true)) {
            $query->where('estado', $estado);
        }

        $observaciones = $query->orderByDesc('fecha')->paginate(15)->withQueryString();

        return view('Rector.observaciones.index', [
            'currentPage' => 'Observaciones',
            'filtros' => $request->only(['q', 'tipo', 'estado']),
            'observaciones' => $observaciones,
            'resumen' => [
                'total' => Observacion::count(),
                'activas' => Observacion::where('estado', 'activa')->count(),
                'disciplinarias' => Observacion::where('tipo_observacion', 'disciplinaria')->count(),
                'positivas' => Observacion::where('tipo_observacion', 'positiva')->count(),
            ],
            'estudiantes' => Estudiante::where('estado_academico', 'activo')->with('usuario')->get(),
            'profesores' => Profesor::where('estado_laboral', 'activo')->with('usuario')->get(),
        ]);
    }

    public function store(ObservacionRequest $request): JsonResponse
    {
        $observacion = Observacion::create($request->validated() + ['estado' => 'activa']);

        return response()->json(['success' => true, 'message' => 'Observación registrada correctamente.', 'id' => $observacion->id_observacion]);
    }

    public function update(ObservacionRequest $request, Observacion $observacion): JsonResponse
    {
        $observacion->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Observación actualizada correctamente.']);
    }

    public function desactivar(Observacion $observacion): JsonResponse
    {
        $observacion->update(['estado' => 'archivada']);

        return response()->json(['success' => true, 'message' => 'Observación archivada correctamente.']);
    }

    public function activar(Observacion $observacion): JsonResponse
    {
        $observacion->update(['estado' => 'activa']);

        return response()->json(['success' => true, 'message' => 'Observación reactivada correctamente.']);
    }
}
