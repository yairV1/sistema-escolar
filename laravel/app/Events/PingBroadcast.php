<?php

namespace App\Events;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento desechable para verificar la cadena Reverb + cola end-to-end
 * (Fase 0). Eliminar una vez confirmado que llega al navegador.
 */
class PingBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Usuario $usuario, public string $mensaje) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Modules.Auth.Models.Usuario.'.$this->usuario->id_usuario)];
    }

    public function broadcastAs(): string
    {
        return 'ping';
    }
}
