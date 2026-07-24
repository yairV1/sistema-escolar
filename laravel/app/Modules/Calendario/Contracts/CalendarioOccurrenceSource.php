<?php

namespace App\Modules\Calendario\Contracts;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\DTO\CalendarioItem;
use Carbon\CarbonImmutable;

/**
 * Una fuente de ocurrencias del calendario (horarios, actividades, eventos
 * reales, y en el futuro Google Calendar/Outlook/reservas de salón...).
 * CalendarioFeedService solo conoce esta interfaz: agregar una fuente nueva
 * no requiere tocar el orquestador (Open/Closed).
 */
interface CalendarioOccurrenceSource
{
    /**
     * @param  array<string, mixed>  $filtros
     * @return CalendarioItem[]
     */
    public function occurrencesBetween(
        CarbonImmutable $desde,
        CarbonImmutable $hasta,
        Usuario $usuario,
        array $filtros
    ): array;
}
