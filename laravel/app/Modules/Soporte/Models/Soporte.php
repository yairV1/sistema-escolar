<?php

namespace App\Modules\Soporte\Models;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Soporte extends Model
{
    protected $table = 'soportes';

    protected $primaryKey = 'id_soporte';

    protected $fillable = [
        'id_usuario',
        'asunto',
        'mensaje',
        'imagen_ruta',
        'imagen_nombre_original',
        'estado',
        'resuelto_por',
        'resuelto_at',
    ];

    protected $casts = [
        'resuelto_at' => 'datetime',
    ];

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function resueltoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'resuelto_por', 'id_usuario');
    }
}
