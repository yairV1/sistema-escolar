<?php

namespace App\Modules\Calendario\Controllers;

use App\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Campanita de notificaciones del panel — panel-wide, no una acción de
 * calendario, pero vive acá porque hoy es el único productor real de
 * Notification (RecordatorioEvento). Si otro módulo empieza a emitir
 * notificaciones propias, esto se promueve a un lugar compartido.
 */
class NotificacionPanelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();

        return response()->json([
            'no_leidas' => $usuario->unreadNotifications()->count(),
            'notificaciones' => $usuario->notifications()->limit(10)->get()->map(fn ($notificacion) => [
                'id' => $notificacion->id,
                'titulo' => $notificacion->data['titulo'] ?? '',
                'url' => $notificacion->data['url'] ?? null,
                'fecha_ocurrencia' => $notificacion->data['fecha_ocurrencia'] ?? null,
                'hora_inicio' => $notificacion->data['hora_inicio'] ?? null,
                'leida' => $notificacion->read_at !== null,
                'creada_hace' => $notificacion->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function leerTodas(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
