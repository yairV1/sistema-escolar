<?php

namespace App\Modules\Calendario\Events;

class EventoMovido extends EventoBroadcastEvent
{
    public function broadcastAs(): string
    {
        return 'evento.movido';
    }
}
