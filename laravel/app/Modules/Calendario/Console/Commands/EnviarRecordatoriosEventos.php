<?php

namespace App\Modules\Calendario\Console\Commands;

use App\Modules\Calendario\DTO\OcurrenciaExpandida;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Models\EventoRecordatorioEnviado;
use App\Modules\Calendario\Notifications\RecordatorioEvento;
use App\Modules\Calendario\Services\RecurrenceExpansionService;
use App\Modules\Auth\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Corre cada 5 minutos (ver routes/console.php). Un objetivo "cuenta" si su
 * momento de disparo cae en (ahora-5min, ahora] — el mismo ancho que la
 * frecuencia de la corrida, así que cada minuto objetivo se evalúa en
 * exactamente una corrida. evento_recordatorios_enviados es la red de
 * seguridad si dos corridas se solapan igual.
 */
class EnviarRecordatoriosEventos extends Command
{
    protected $signature = 'calendario:enviar-recordatorios';

    protected $description = 'Envía (mail + database) los recordatorios de eventos cuyo momento de aviso cae en la ventana actual.';

    public function handle(RecurrenceExpansionService $expansor): int
    {
        $ahora = CarbonImmutable::now();
        $ventanaDesde = $ahora->subMinutes(5);
        $maxMinutosAntes = max(array_keys(config('calendario.recordatorio_opciones_minutos')));
        $horizonte = $ahora->addMinutes($maxMinutosAntes);

        $eventos = Evento::query()->activos()
            ->whereNotNull('recordatorio_minutos_antes')
            ->where('fecha_inicio', '<=', $horizonte->toDateString())
            ->where(fn ($q) => $q->whereNull('fecha_fin_recurrencia')->orWhere('fecha_fin_recurrencia', '>=', $ahora->toDateString()))
            ->with(['excepciones', 'participantes.usuario', 'creador'])
            ->get();

        // RecurrenceExpansionService asume fronteras a nivel de día (mismo
        // contrato que ya usan CalendarioFeedService/EventoOccurrenceSource,
        // que siempre le pasan $desde/$hasta como límites de fecha, nunca un
        // instante con hora) — comparar "ahora" (con hora) directo contra un
        // fecha_fin a medianoche rompía la superposición de eventos de hoy.
        // El filtrado fino por minuto ya lo hace $objetivo más abajo.
        $desdeExpansion = $ahora->startOfDay();
        $hastaExpansion = $horizonte->endOfDay();

        $enviados = 0;

        foreach ($eventos as $evento) {
            foreach ($expansor->expandir($evento, $desdeExpansion, $hastaExpansion) as $ocurrencia) {
                $inicio = $this->momentoInicio($evento, $ocurrencia);

                foreach ($evento->recordatorio_minutos_antes as $minutos) {
                    $objetivo = $inicio->subMinutes((int) $minutos);

                    if ($objetivo->gt($ahora) || $objetivo->lte($ventanaDesde)) {
                        continue;
                    }

                    foreach ($this->destinatarios($evento) as $usuario) {
                        $yaEnviado = EventoRecordatorioEnviado::query()->where([
                            'id_evento' => $evento->id_evento,
                            'id_usuario' => $usuario->id_usuario,
                            'fecha_ocurrencia' => $ocurrencia->fechaOriginal->toDateString(),
                            'minutos_antes' => $minutos,
                        ])->exists();

                        if ($yaEnviado) {
                            continue;
                        }

                        $usuario->notify(new RecordatorioEvento($evento, $ocurrencia, (int) $minutos));

                        EventoRecordatorioEnviado::create([
                            'id_evento' => $evento->id_evento,
                            'id_usuario' => $usuario->id_usuario,
                            'fecha_ocurrencia' => $ocurrencia->fechaOriginal->toDateString(),
                            'minutos_antes' => $minutos,
                        ]);

                        $enviados++;
                    }
                }
            }
        }

        $this->info("Recordatorios enviados: {$enviados}.");

        return self::SUCCESS;
    }

    private function momentoInicio(Evento $evento, OcurrenciaExpandida $ocurrencia): CarbonImmutable
    {
        if ($evento->todo_el_dia || ! $ocurrencia->horaInicio) {
            return $ocurrencia->fechaInicio->startOfDay();
        }

        return CarbonImmutable::parse($ocurrencia->fechaInicio->toDateString().' '.$ocurrencia->horaInicio);
    }

    /**
     * Mismos destinatarios que Evento::canalesBroadcast() (Fase 4): el
     * creador siempre, y los participantes solo si es compartido — nunca
     * "todo el que puede ver el evento" (eso sería difusión institucional,
     * ya cubierta por Comunicados, no un recordatorio personal).
     *
     * @return Collection<int, Usuario>
     */
    private function destinatarios(Evento $evento): Collection
    {
        $destinatarios = collect([$evento->creador])->filter();

        if ($evento->visibilidad === 'compartido') {
            $destinatarios = $destinatarios->concat(
                $evento->participantes->pluck('usuario')->filter()
            );
        }

        return $destinatarios->unique('id_usuario');
    }
}
