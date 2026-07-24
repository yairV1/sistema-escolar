<?php

namespace App\Modules\Calendario\Mail;

use App\Modules\Calendario\DTO\OcurrenciaExpandida;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mismo patrón visual que ComunicadoMail — identidad de marca consistente en todo correo del sistema. */
class RecordatorioEventoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Evento $evento,
        public OcurrenciaExpandida $ocurrencia,
        public int $minutosAntes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio: '.$this->evento->titulo.' — '.ColegioConfiguracion::singleton()->nombre_colegio,
        );
    }

    public function content(): Content
    {
        $configuracion = ColegioConfiguracion::singleton();
        $opciones = config('calendario.recordatorio_opciones_minutos');

        return new Content(
            view: 'emails.recordatorio-evento',
            with: [
                'titulo' => $this->evento->titulo,
                'descripcion' => $this->evento->descripcion,
                'fechaLabel' => $this->ocurrencia->fechaInicio->format('d/m/Y'),
                'horaLabel' => $this->evento->todo_el_dia ? 'Todo el día' : ($this->ocurrencia->horaInicio ?? ''),
                'ubicacion' => $this->evento->ubicacion ?: $this->evento->salon,
                'antelacionLabel' => $opciones[$this->minutosAntes] ?? $this->minutosAntes.' minutos antes',
                'urlCalendario' => route('calendario.index'),
                'colegioNombre' => $configuracion->nombre_colegio,
                'logoUrl' => $configuracion->logo_url,
                'direccion' => $configuracion->direccion,
                'telefono' => $configuracion->telefono,
                'web' => $configuracion->sitio_web,
            ],
        );
    }
}
