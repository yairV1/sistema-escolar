<?php

namespace App\Modules\Calendario\Notifications;

use App\Modules\Calendario\DTO\OcurrenciaExpandida;
use App\Modules\Calendario\Mail\RecordatorioEventoMail;
use App\Modules\Calendario\Models\Evento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

/** Primera Notification real del proyecto — Usuario::Notifiable existía desde siempre sin un consumidor. */
class RecordatorioEvento extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Evento $evento,
        public OcurrenciaExpandida $ocurrencia,
        public int $minutosAntes,
    ) {}

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Devuelve un Mailable propio (no MailMessage genérico) para mantener la
     * identidad visual de ComunicadoMail. A diferencia de un MailMessage, el
     * canal mail NO asigna destinatario automáticamente cuando toMail()
     * devuelve un Mailable — hay que fijarlo explícitamente con ->to().
     */
    public function toMail(object $notifiable): Mailable
    {
        return (new RecordatorioEventoMail($this->evento, $this->ocurrencia, $this->minutosAntes))
            ->to($notifiable->correo);
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'id_evento' => $this->evento->id_evento,
            'titulo' => $this->evento->titulo,
            'fecha_ocurrencia' => $this->ocurrencia->fechaInicio->toDateString(),
            'hora_inicio' => $this->evento->todo_el_dia ? null : $this->ocurrencia->horaInicio,
        ];
    }
}
