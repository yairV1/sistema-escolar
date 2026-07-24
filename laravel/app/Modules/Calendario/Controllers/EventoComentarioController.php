<?php

namespace App\Modules\Calendario\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Models\EventoComentario;
use App\Modules\Calendario\Requests\EventoComentarioRequest;
use App\Modules\Calendario\Services\EventoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventoComentarioController extends Controller
{
    public function __construct(private EventoService $eventoService) {}

    public function store(EventoComentarioRequest $request, Evento $evento): JsonResponse
    {
        $comentario = $this->eventoService->agregarComentario($evento, $request->input('comentario'), $request->user());
        $comentario->load('usuario');

        return response()->json([
            'success' => true,
            'message' => 'Comentario agregado.',
            'comentario' => [
                'id_comentario' => $comentario->id_comentario,
                'comentario' => $comentario->comentario,
                'autor' => trim($comentario->usuario->nombres.' '.$comentario->usuario->apellidos),
                'es_propio' => $comentario->id_usuario === $request->user()->id_usuario,
                'fecha' => $comentario->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function destroy(Request $request, EventoComentario $comentario): JsonResponse
    {
        $puedeEliminar = $comentario->id_usuario === $request->user()->id_usuario
            || $request->user()->can('update', $comentario->evento);

        abort_unless($puedeEliminar, 403);

        $this->eventoService->eliminarComentario($comentario);

        return response()->json(['success' => true, 'message' => 'Comentario eliminado.']);
    }
}
