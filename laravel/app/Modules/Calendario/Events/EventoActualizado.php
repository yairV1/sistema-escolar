<?php

namespace App\Modules\Calendario\Events;

class EventoActualizado extends EventoBroadcastEvent
{
    public function broadcastAs(): string
    {
        return 'evento.actualizado';
    }
}
