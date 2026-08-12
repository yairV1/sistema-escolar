<?php

namespace App\Modules\Soporte\Notifications;

use App\Modules\Soporte\Models\Soporte;
use Illuminate\Notifications\Notification;

/** Solo canal database, sin ShouldQueue: QUEUE_CONNECTION=database requiere un worker corriendo, y esta notificación debe aparecer de inmediato en la campana del superadmin al enviarse un ticket. */
class SoporteCreado extends Notification
{
    public function __construct(public Soporte $soporte) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => 'Nuevo soporte: '.$this->soporte->asunto,
            'url' => route('soportes.show', $this->soporte),
        ];
    }
}
