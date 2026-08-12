<?php

namespace App\Modules\Soporte\Models;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Soporte extends Model
{
    protected $table = 'soportes';

    protected $primaryKey = 'id_soporte';

    protected $fillable = [
        'id_usuario',
        'asunto',
        'mensaje',
        'imagen',
        'estado',
        'resuelto_por',
        'resuelto_at',
    ];

    protected $casts = [
        'resuelto_at' => 'datetime',
    ];

    /** Mismo patrón que ColegioConfiguracion::logoUrl() / Institucion::logoUrl(). */
    protected function imagenUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->imagen ? Storage::disk('public')->url($this->imagen) : null,
        );
    }

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function resueltoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'resuelto_por', 'id_usuario');
    }
}
