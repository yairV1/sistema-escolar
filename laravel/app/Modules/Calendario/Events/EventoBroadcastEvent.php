<?php

namespace App\Modules\Calendario\Events;

use App\Modules\Calendario\Models\Evento;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Base compartida de los 4 eventos de dominio del calendario. El payload es
 * deliberadamente mínimo (id + acción, nunca título/fecha/hora): el
 * cliente reacciona con un refetch del feed, que ya resuelve visibilidad y
 * recurrencia/excepciones correctamente en el servidor — parchear la UI a
 * partir del payload del socket duplicaría RecurrenceExpansionService en
 * JS, exactamente la complejidad que este módulo evita en cada fase.
 *
 * ShouldDispatchAfterCommit (verificado contra el Dispatcher real de
 * Laravel) difiere el envío hasta que la transacción de EventoService haga
 * commit, y lo descarta si hace rollback — sin que EventoService tenga que
 * envolver nada explícitamente.
 */
abstract class EventoBroadcastEvent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Evento $evento) {}

    public function broadcastOn(): array
    {
        return $this->evento->canalesBroadcast();
    }

    public function broadcastWith(): array
    {
        return [
            'id_evento' => $this->evento->id_evento,
            'accion' => $this->broadcastAs(),
        ];
    }

    abstract public function broadcastAs(): string;
}
