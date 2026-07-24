<?php

namespace App\Modules\Calendario\DTO;

/**
 * Contrato único que deben producir las tres fuentes del feed
 * (horarios derivados, actividades derivadas, eventos reales) para que
 * CalendarioFeedService las pueda mezclar sin saber de dónde vino cada una.
 * `toArray()` ya entrega el shape exacto que espera FullCalendar.
 */
final readonly class CalendarioItem
{
    public function __construct(
        public string $id,
        public string $title,
        public string $start,
        public ?string $end,
        public bool $allDay,
        public string $color,
        public string $tipo,
        public bool $editable,
        public array $extendedProps = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start,
            'end' => $this->end,
            'allDay' => $this->allDay,
            'color' => $this->color,
            'editable' => $this->editable,
            'extendedProps' => array_merge(['tipo' => $this->tipo], $this->extendedProps),
        ];
    }
}
