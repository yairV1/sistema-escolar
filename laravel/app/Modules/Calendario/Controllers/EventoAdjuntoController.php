<?php

namespace App\Modules\Calendario\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Models\EventoAdjunto;
use App\Modules\Calendario\Requests\EventoAdjuntoRequest;
use App\Modules\Calendario\Services\EventoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventoAdjuntoController extends Controller
{
    public function __construct(private EventoService $eventoService) {}

    public function store(EventoAdjuntoRequest $request, Evento $evento): JsonResponse
    {
        $adjunto = $this->eventoService->adjuntar($evento, $request->file('adjunto'), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Archivo adjuntado correctamente.',
            'adjunto' => [
                'id_adjunto' => $adjunto->id_adjunto,
                'nombre_original' => $adjunto->nombre_original,
                'url' => Storage::disk('public')->url($adjunto->ruta),
                'tamano_bytes' => $adjunto->tamano_bytes,
            ],
        ]);
    }

    public function destroy(Request $request, EventoAdjunto $adjunto): JsonResponse
    {
        abort_unless($request->user()->can('update', $adjunto->evento), 403);

        $this->eventoService->quitarAdjunto($adjunto);

        return response()->json(['success' => true, 'message' => 'Adjunto eliminado correctamente.']);
    }
}
