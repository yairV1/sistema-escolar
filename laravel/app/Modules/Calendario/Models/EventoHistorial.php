<?php

namespace App\Modules\Calendario\Models;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Bitácora append-only de mutaciones de un Evento. Sin updated_at. */
class EventoHistorial extends Model
{
    protected $table = 'evento_historial';

    protected $primaryKey = 'id_historial';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_evento',
        'id_usuario',
        'accion',
        'cambios',
    ];

    protected $casts = [
        'cambios' => 'array',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'id_evento', 'id_evento');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
