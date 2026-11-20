<?php

namespace App\Modules\Calendario\Controllers;

use App\Core\Archivos\ArchivoPrivado;
use App\Core\Http\Controllers\Controller;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Models\EventoAdjunto;
use App\Modules\Calendario\Requests\EventoAdjuntoRequest;
use App\Modules\Calendario\Services\EventoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                'url' => route('calendario.adjuntos.show', $adjunto),
                'tamano_bytes' => $adjunto->tamano_bytes,
            ],
        ]);
    }

    public function show(Request $request, EventoAdjunto $adjunto, ArchivoPrivado $archivos): BinaryFileResponse
    {
        abort_unless($request->user()->can('view', $adjunto->evento), 404);

        return $archivos->inline($adjunto->ruta, 'eventos');
    }

    public function destroy(Request $request, EventoAdjunto $adjunto): JsonResponse
    {
        abort_unless($request->user()->can('update', $adjunto->evento), 403);

        $this->eventoService->quitarAdjunto($adjunto);

        return response()->json(['success' => true, 'message' => 'Adjunto eliminado correctamente.']);
    }
}
