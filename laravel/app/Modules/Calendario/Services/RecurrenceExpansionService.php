<?php

namespace App\Modules\Calendario\Services;

use App\Modules\Calendario\DTO\OcurrenciaExpandida;
use App\Modules\Calendario\Models\Evento;
use Carbon\CarbonImmutable;

/**
 * Expande la regla de recurrencia de un Evento en ocurrencias concretas
 * dentro de [desde, hasta] — mismo principio que HorarioOccurrenceSource:
 * expansión en tiempo de consulta, acotada al rango pedido, nunca
 * materializada. Aplica evento_excepciones (cancelada/movida) sobre las
 * fechas candidatas antes de devolver el resultado final.
 */
class RecurrenceExpansionService
{
    /** dayOfWeekIso (1=lunes..7=domingo) -> nombre. Mismo mapeo que HorarioOccurrenceSource, evita depender del locale de Carbon. */
    private const DIA_ISO_A_NOMBRE = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        7 => 'domingo',
    ];

    /** @return OcurrenciaExpandida[] */
    public function expandir(Evento $evento, CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        $inicioEvento = CarbonImmutable::parse($evento->fecha_inicio->toDateString());
        $finEvento = CarbonImmutable::parse($evento->fecha_fin->toDateString());
        $duracionDias = $inicioEvento->diffInDays($finEvento);

        if ($evento->tipo_recurrencia === 'ninguna') {
            $seSuperpone = $inicioEvento->lte($hasta) && $finEvento->gte($desde);

            return $seSuperpone ? [new OcurrenciaExpandida(
                fechaOriginal: $inicioEvento,
                fechaInicio: $inicioEvento,
                fechaFin: $finEvento,
                horaInicio: $evento->hora_inicio,
                horaFin: $evento->hora_fin,
                movida: false,
            )] : [];
        }

        $limite = $evento->fecha_fin_recurrencia
            ? CarbonImmutable::parse($evento->fecha_fin_recurrencia->toDateString())->min($hasta)
            : $hasta;

        if ($inicioEvento->gt($limite)) {
            return [];
        }

        $intervalo = max(1, $evento->intervalo_recurrencia ?? 1);
        $fechasCandidatas = match ($evento->tipo_recurrencia) {
            'diaria' => $this->candidatasDiaria($inicioEvento, $desde, $limite, $intervalo),
            'semanal' => $this->candidatasSemanal($inicioEvento, $desde, $limite, $intervalo, $evento->dias_semana_recurrencia ?? []),
            'mensual' => $this->candidatasMensual($inicioEvento, $desde, $limite, $intervalo),
            default => [],
        };

        return $this->aplicarExcepciones($evento, $fechasCandidatas, $duracionDias);
    }

    /** @param CarbonImmutable[] $fechasCandidatas @return OcurrenciaExpandida[] */
    private function aplicarExcepciones(Evento $evento, array $fechasCandidatas, int $duracionDias): array
    {
        $excepciones = $evento->relationLoaded('excepciones') ? $evento->excepciones : $evento->excepciones()->get();
        $excepcionesPorFecha = $excepciones->keyBy(fn ($e) => $e->fecha_original->toDateString());

        $ocurrencias = [];
        foreach ($fechasCandidatas as $fecha) {
            $excepcion = $excepcionesPorFecha->get($fecha->toDateString());

            if ($excepcion?->tipo === 'cancelada') {
                continue;
            }

            if ($excepcion?->tipo === 'movida') {
                $ocurrencias[] = new OcurrenciaExpandida(
                    fechaOriginal: $fecha,
                    fechaInicio: CarbonImmutable::parse($excepcion->nueva_fecha_inicio->toDateString()),
                    fechaFin: CarbonImmutable::parse($excepcion->nueva_fecha_fin->toDateString()),
                    horaInicio: $excepcion->nueva_hora_inicio,
                    horaFin: $excepcion->nueva_hora_fin,
                    movida: true,
                );

                continue;
            }

            $ocurrencias[] = new OcurrenciaExpandida(
                fechaOriginal: $fecha,
                fechaInicio: $fecha,
                fechaFin: $fecha->addDays($duracionDias),
                horaInicio: $evento->hora_inicio,
                horaFin: $evento->hora_fin,
                movida: false,
            );
        }

        return $ocurrencias;
    }

    /** @return CarbonImmutable[] */
    private function candidatasDiaria(CarbonImmutable $inicio, CarbonImmutable $desde, CarbonImmutable $limite, int $intervalo): array
    {
        $fechas = [];
        for ($cursor = $desde->gt($inicio) ? $desde : $inicio; $cursor->lte($limite); $cursor = $cursor->addDay()) {
            if ($inicio->diffInDays($cursor) % $intervalo === 0) {
                $fechas[] = $cursor;
            }
        }

        return $fechas;
    }

    /** @return CarbonImmutable[] */
    private function candidatasSemanal(CarbonImmutable $inicio, CarbonImmutable $desde, CarbonImmutable $limite, int $intervalo, array $diasSemana): array
    {
        $diasPermitidos = empty($diasSemana) ? [self::DIA_ISO_A_NOMBRE[$inicio->dayOfWeekIso]] : $diasSemana;
        $inicioSemana = $inicio->startOfWeek(CarbonImmutable::MONDAY);

        $fechas = [];
        for ($cursor = $desde->gt($inicio) ? $desde : $inicio; $cursor->lte($limite); $cursor = $cursor->addDay()) {
            $semanasTranscurridas = $inicioSemana->diffInWeeks($cursor->startOfWeek(CarbonImmutable::MONDAY));
            $nombreDia = self::DIA_ISO_A_NOMBRE[$cursor->dayOfWeekIso] ?? null;

            if ($semanasTranscurridas % $intervalo === 0 && in_array($nombreDia, $diasPermitidos, true)) {
                $fechas[] = $cursor;
            }
        }

        return $fechas;
    }

    /** @return CarbonImmutable[] */
    private function candidatasMensual(CarbonImmutable $inicio, CarbonImmutable $desde, CarbonImmutable $limite, int $intervalo): array
    {
        $fechas = [];
        for ($cursor = $desde->gt($inicio) ? $desde : $inicio; $cursor->lte($limite); $cursor = $cursor->addDay()) {
            $mesesTranscurridos = ($cursor->year - $inicio->year) * 12 + ($cursor->month - $inicio->month);
            if ($mesesTranscurridos < 0 || $mesesTranscurridos % $intervalo !== 0) {
                continue;
            }

            // Clamp al último día del mes si el mes actual es más corto que el día original
            // (ej. evento del 31 de enero -> 28/29 de febrero), en vez de desbordar al mes siguiente.
            $diaObjetivo = min($inicio->day, $cursor->daysInMonth);
            if ($cursor->day === $diaObjetivo) {
                $fechas[] = $cursor;
            }
        }

        return $fechas;
    }
}
