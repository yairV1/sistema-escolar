<?php

namespace App\Modules\Colegio\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Información institucional del colegio: registro único (id_configuracion = 1),
 * sembrado por la migración. No es un CRUD de múltiples filas.
 */
class ColegioConfiguracion extends Model
{
    protected $table = 'colegio_configuracion';

    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'nombre_colegio',
        'logo',
        'direccion',
        'ciudad',
        'departamento',
        'pais',
        'telefono',
        'email_institucional',
        'sitio_web',
        'descripcion',
    ];

    public static function singleton(): self
    {
        return static::query()->findOrFail(1);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ColegioImagen::class, 'id_configuracion', 'id_configuracion')->orderBy('orden');
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo ? Storage::disk('public')->url($this->logo) : null,
        );
    }
}
