<?php

namespace App\Modules\Calendario\Models;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Registro de idempotencia: qué combinación evento+usuario+ocurrencia+offset ya recibió su recordatorio. Append-only, sin updated_at. */
class EventoRecordatorioEnviado extends Model
{
    protected $table = 'evento_recordatorios_enviados';

    protected $primaryKey = 'id_recordatorio_enviado';

    public const CREATED_AT = 'enviado_at';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_evento',
        'id_usuario',
        'fecha_ocurrencia',
        'minutos_antes',
    ];

    protected $casts = [
        'fecha_ocurrencia' => 'date',
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
