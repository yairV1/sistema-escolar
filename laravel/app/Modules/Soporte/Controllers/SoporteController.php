<?php

namespace App\Modules\Soporte\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Soporte\Models\Soporte;
use App\Modules\Soporte\Requests\SoporteStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SoporteController extends Controller
{
    public function create(): View
    {
        return view('Soporte.create', [
            'currentPage' => 'SoporteCreate',
        ]);
    }

    public function store(SoporteStoreRequest $request): JsonResponse
    {
        Soporte::create([
            'id_usuario' => auth()->id(),
            'asunto' => $request->string('asunto'),
            'mensaje' => $request->string('mensaje'),
            'estado' => 'nuevo',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tu soporte fue enviado. El equipo del sistema lo revisará pronto.',
        ]);
    }

    public function index(Request $request): View
    {
        $estado = $request->query('estado');

        $soportes = Soporte::query()
            ->with('remitente')
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->orderByDesc('created_at')
            ->get();

        return view('Soporte.index', [
            'currentPage' => 'SoportesIndex',
            'soportes' => $soportes,
            'estadoSeleccionado' => $estado,
            'kpis' => [
                'nuevos' => Soporte::where('estado', 'nuevo')->count(),
                'leidos' => Soporte::where('estado', 'leido')->count(),
                'resueltos' => Soporte::where('estado', 'resuelto')->count(),
            ],
        ]);
    }

    public function show(Soporte $soporte): View
    {
        if ($soporte->estado === 'nuevo') {
            $soporte->update(['estado' => 'leido']);
        }

        return view('Soporte.show', [
            'currentPage' => 'SoportesIndex',
            'soporte' => $soporte->load('remitente', 'resueltoPor'),
        ]);
    }

    public function resolver(Soporte $soporte): JsonResponse
    {
        $soporte->update([
            'estado' => 'resuelto',
            'resuelto_por' => auth()->id(),
            'resuelto_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Soporte marcado como resuelto.',
        ]);
    }
}
