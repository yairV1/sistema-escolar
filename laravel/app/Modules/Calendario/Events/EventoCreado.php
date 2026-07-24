<?php

namespace App\Modules\Calendario\Events;

class EventoCreado extends EventoBroadcastEvent
{
    public function broadcastAs(): string
    {
        return 'evento.creado';
    }
}
