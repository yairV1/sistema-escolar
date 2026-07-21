<?php

namespace App\Modules\Colegio\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ColegioImagen extends Model
{
    public const TIPOS_LABELS = [
        'fachada' => 'Fachada',
        'patio' => 'Patio',
        'aulas' => 'Aulas',
        'deportes' => 'Deportes',
        'biblioteca' => 'Biblioteca',
        'otro' => 'Otro',
    ];

    protected $table = 'colegio_imagenes';

    protected $primaryKey = 'id_imagen';

    protected $fillable = [
        'id_configuracion',
        'imagen',
        'tipo',
        'descripcion',
        'orden',
    ];

    public function configuracion(): BelongsTo
    {
        return $this->belongsTo(ColegioConfiguracion::class, 'id_configuracion', 'id_configuracion');
    }

    protected function imagenUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->imagen ? Storage::disk('public')->url($this->imagen) : null,
        );
    }
}
