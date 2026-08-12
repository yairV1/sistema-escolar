<?php

namespace App\Modules\Soporte\Notifications;

use App\Modules\Soporte\Models\Soporte;
use Illuminate\Notifications\Notification;

/** Avisa al remitente cuando su ticket cambia de estado (leído por soporte o resuelto). Sin ShouldQueue por el mismo motivo que SoporteCreado. */
class SoporteEstadoActualizado extends Notification
{
    public function __construct(public Soporte $soporte) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $mensajes = [
            'leido' => 'Tu soporte "'.$this->soporte->asunto.'" está en revisión.',
            'resuelto' => 'Tu soporte "'.$this->soporte->asunto.'" fue resuelto.',
        ];

        return [
            'titulo' => $mensajes[$this->soporte->estado] ?? 'Actualización de tu soporte',
            'url' => route('soporte.mis-solicitudes'),
        ];
    }
}
