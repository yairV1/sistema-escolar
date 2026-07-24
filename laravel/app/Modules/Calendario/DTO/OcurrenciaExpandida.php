<?php

namespace App\Modules\Calendario\DTO;

use Carbon\CarbonImmutable;

/**
 * Contrato interno entre RecurrenceExpansionService y su único consumidor
 * (EventoOccurrenceSource) — no el "contrato del feed" (ese es
 * CalendarioItem). `fechaOriginal` se conserva siempre, incluso cuando la
 * ocurrencia fue movida, porque es la clave con la que el frontend
 * identifica/reagenda esa ocurrencia puntual en evento_excepciones.
 */
final readonly class OcurrenciaExpandida
{
    public function __construct(
        public CarbonImmutable $fechaOriginal,
        public CarbonImmutable $fechaInicio,
        public CarbonImmutable $fechaFin,
        public ?string $horaInicio,
        public ?string $horaFin,
        public bool $movida,
    ) {}
}
