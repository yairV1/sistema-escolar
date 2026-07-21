<?php

namespace App\Modules\Comunicados\Mail;

use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\Comunicados\Models\Notificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComunicadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $titulo,
        public string $mensaje,
        public string $tipoNotificacion,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->titulo.' — '.ColegioConfiguracion::singleton()->nombre_colegio,
        );
    }

    public function content(): Content
    {
        $configuracion = ColegioConfiguracion::singleton();

        return new Content(
            view: 'emails.comunicado',
            with: [
                'titulo' => $this->titulo,
                'mensaje' => $this->mensaje,
                'tipoNotificacionLabel' => Notificacion::TIPOS_LABELS[$this->tipoNotificacion] ?? ucfirst($this->tipoNotificacion),
                'colegioNombre' => $configuracion->nombre_colegio,
                'logoUrl' => $configuracion->logo_url,
                'direccion' => $configuracion->direccion,
                'telefono' => $configuracion->telefono,
                'web' => $configuracion->sitio_web,
            ],
        );
    }
}
