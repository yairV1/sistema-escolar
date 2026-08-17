<?php

namespace App\Modules\Docente\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Docente\Requests\AnotacionRequest;
use App\Modules\Observaciones\Models\Observacion;
use App\Modules\Usuarios\Models\Estudiante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnotacionesController extends Controller
{
    public function index(Request $request): View
    {
        $profesor = auth()->user()->profesor;

        abort_if(! $profesor, 404, 'Tu usuario no tiene un perfil de profesor asociado. Contacta al colegio.');

        $query = Observacion::where('id_profesor', $profesor->id_profesor)
            ->with('estudiante.usuario');

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

        $anotaciones = $query->orderByDesc('fecha')->paginate(15)->withQueryString();

        return view('Docente.anotaciones.index', [
            'currentPage' => 'DocenteAnotaciones',
            'filtros' => $request->only(['q', 'tipo', 'estado']),
            'anotaciones' => $anotaciones,
            'resumen' => [
                'total' => Observacion::where('id_profesor', $profesor->id_profesor)->count(),
                'activas' => Observacion::where('id_profesor', $profesor->id_profesor)->where('estado', 'activa')->count(),
                'disciplinarias' => Observacion::where('id_profesor', $profesor->id_profesor)->where('tipo_observacion', 'disciplinaria')->count(),
                'positivas' => Observacion::where('id_profesor', $profesor->id_profesor)->where('tipo_observacion', 'positiva')->count(),
            ],
            'estudiantes' => Estudiante::where('estado_academico', 'activo')->with('usuario')->get(),
        ]);
    }

    public function store(AnotacionRequest $request): JsonResponse
    {
        $profesor = auth()->user()->profesor;

        abort_if(! $profesor, 404, 'Tu usuario no tiene un perfil de profesor asociado. Contacta al colegio.');

        $anotacion = Observacion::create($request->validated() + [
            'id_profesor' => $profesor->id_profesor,
            'estado' => 'activa',
        ]);

        return response()->json(['success' => true, 'message' => 'Anotación registrada correctamente.', 'id' => $anotacion->id_observacion]);
    }

    public function update(AnotacionRequest $request, Observacion $observacion): JsonResponse
    {
        abort_unless($observacion->id_profesor === auth()->user()->profesor?->id_profesor, 403);

        $observacion->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Anotación actualizada correctamente.']);
    }

    public function desactivar(Observacion $observacion): JsonResponse
    {
        abort_unless($observacion->id_profesor === auth()->user()->profesor?->id_profesor, 403);

        $observacion->update(['estado' => 'archivada']);

        return response()->json(['success' => true, 'message' => 'Anotación archivada correctamente.']);
    }

    public function activar(Observacion $observacion): JsonResponse
    {
        abort_unless($observacion->id_profesor === auth()->user()->profesor?->id_profesor, 403);

        $observacion->update(['estado' => 'activa']);

        return response()->json(['success' => true, 'message' => 'Anotación reactivada correctamente.']);
    }
}
