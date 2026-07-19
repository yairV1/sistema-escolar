<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Boletin extends Model
{
    protected $table = 'boletines';

    protected $primaryKey = 'id_boletin';

    public $timestamps = false;

    public const ESTADOS = ['borrador', 'publicado', 'anulado'];

    protected $fillable = [
        'id_estudiante',
        'id_periodo',
        'promedio_general',
        'puesto_curso',
        'observaciones_gral',
        'estado',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }

    public function detalle(): HasMany
    {
        return $this->hasMany(BoletinDetalle::class, 'id_boletin', 'id_boletin');
    }
}
