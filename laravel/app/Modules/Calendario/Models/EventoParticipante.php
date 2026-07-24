<?php

namespace App\Modules\Calendario\Models;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoParticipante extends Model
{
    protected $table = 'evento_participantes';

    protected $primaryKey = 'id_participante';

    protected $fillable = [
        'id_evento',
        'id_usuario',
        'rol_participacion',
        'respuesta',
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
