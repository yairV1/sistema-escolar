<?php

namespace App\Modules\Auth\Mail;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecuperarPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $enlace,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recupera tu contraseña — Colegio San Cristóbal',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recuperar-password',
            with: [
                'nombre' => trim($this->usuario->nombres.' '.$this->usuario->apellidos),
                'enlace' => $this->enlace,
            ],
        );
    }
}
