<?php

namespace App\Modules\Calendario\Services;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use Carbon\CarbonImmutable;

/**
 * Genera un .ics (RFC 5545) reutilizando CalendarioFeedService::feed() —
 * el mismo array que ya consume FullCalendar. Sin librería nueva de
 * Composer: el subconjunto que necesita un calendario escolar (VEVENT
 * simple, ya expandido en ocurrencias concretas por el feed) es
 * suficientemente simple para generar a mano.
 */
class IcsExportService
{
    public function __construct(private CalendarioFeedService $feed) {}

    public function generar(Usuario $usuario, CarbonImmutable $desde, CarbonImmutable $hasta): string
    {
        $items = $this->feed->feed($desde, $hasta, $usuario);
        $nombreColegio = ColegioConfiguracion::singleton()->nombre_colegio;

        $lineas = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//'.$this->escapar($nombreColegio).'//Calendario Institucional//ES',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escapar($nombreColegio),
        ];

        foreach ($items as $item) {
            $lineas = array_merge($lineas, $this->vevent($item));
        }

        $lineas[] = 'END:VCALENDAR';

        return implode("\r\n", array_map($this->plegar(...), $lineas))."\r\n";
    }

    /** @return string[] */
    private function vevent(array $item): array
    {
        $allDay = (bool) $item['allDay'];
        $ubicacion = $item['extendedProps']['ubicacion'] ?? $item['extendedProps']['salon'] ?? null;

        $lineas = [
            'BEGIN:VEVENT',
            'UID:'.$item['id'].'@calendario-institucional',
            'DTSTAMP:'.CarbonImmutable::now()->utc()->format('Ymd\THis\Z'),
            'DTSTART'.($allDay ? ';VALUE=DATE:' : ':').$this->fecha($item['start'], $allDay),
        ];

        if ($item['end']) {
            $lineas[] = 'DTEND'.($allDay ? ';VALUE=DATE:' : ':').$this->fecha($item['end'], $allDay);
        }

        $lineas[] = 'SUMMARY:'.$this->escapar($item['title']);

        if ($ubicacion) {
            $lineas[] = 'LOCATION:'.$this->escapar($ubicacion);
        }

        $lineas[] = 'END:VEVENT';

        return $lineas;
    }

    private function fecha(string $isoString, bool $allDay): string
    {
        $fecha = CarbonImmutable::parse($isoString);

        return $allDay ? $fecha->format('Ymd') : $fecha->format('Ymd\THis');
    }

    private function escapar(string $texto): string
    {
        return str_replace(["\\", ",", ";", "\n"], ["\\\\", "\\,", "\\;", "\\n"], $texto);
    }

    /** Plegado de línea RFC 5545: máx. 75 octetos, continuación con un espacio inicial. */
    private function plegar(string $linea): string
    {
        if (strlen($linea) <= 75) {
            return $linea;
        }

        $trozos = [];
        while (strlen($linea) > 75) {
            $trozos[] = substr($linea, 0, 75);
            $linea = ' '.substr($linea, 75);
        }
        $trozos[] = $linea;

        return implode("\r\n", $trozos);
    }
}
