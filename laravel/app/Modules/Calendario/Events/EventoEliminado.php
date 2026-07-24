<?php

namespace App\Modules\Calendario\Events;

class EventoEliminado extends EventoBroadcastEvent
{
    public function broadcastAs(): string
    {
        return 'evento.eliminado';
    }
}
