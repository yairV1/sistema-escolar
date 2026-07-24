<?php

namespace App\Modules\Calendario\Services;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Contracts\CalendarioOccurrenceSource;
use Carbon\CarbonImmutable;

/**
 * Orquestador único que consume FullCalendar vía CalendarioController::feed().
 * No sabe nada de horarios, actividades ni eventos — solo itera fuentes que
 * implementan CalendarioOccurrenceSource y mezcla el resultado. Agregar una
 * fuente nueva (Google Calendar, Outlook, reservas de salón...) es una
 * clase más en el binding, cero cambios aquí (Open/Closed).
 */
class CalendarioFeedService
{
    /** @param CalendarioOccurrenceSource[] $fuentes */
    public function __construct(private array $fuentes) {}

    public function feed(CarbonImmutable $desde, CarbonImmutable $hasta, Usuario $usuario, array $filtros = []): array
    {
        $items = [];

        foreach ($this->fuentes as $fuente) {
            foreach ($fuente->occurrencesBetween($desde, $hasta, $usuario, $filtros) as $item) {
                $items[] = $item;
            }
        }

        usort($items, fn ($a, $b) => $a->start <=> $b->start);

        return array_map(fn ($item) => $item->toArray(), $items);
    }
}
