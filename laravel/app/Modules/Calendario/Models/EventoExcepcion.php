<?php

namespace App\Modules\Calendario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Override puntual de una sola ocurrencia de un Evento recurrente: cancelada o movida. Nunca toca la plantilla. */
class EventoExcepcion extends Model
{
    protected $table = 'evento_excepciones';

    protected $primaryKey = 'id_excepcion';

    protected $fillable = [
        'id_evento',
        'fecha_original',
        'tipo',
        'nueva_fecha_inicio',
        'nueva_hora_inicio',
        'nueva_fecha_fin',
        'nueva_hora_fin',
    ];

    protected $casts = [
        'fecha_original' => 'date',
        'nueva_fecha_inicio' => 'date',
        'nueva_fecha_fin' => 'date',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'id_evento', 'id_evento');
    }
}
