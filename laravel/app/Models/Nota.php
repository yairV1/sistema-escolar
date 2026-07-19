<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nota extends Model
{
    protected $table = 'detalle_notas';

    protected $primaryKey = 'id_nota';

    public $timestamps = false;

    protected $fillable = [
        'id_actividad',
        'id_estudiante',
        'nota',
        'observacion',
        'estado',
    ];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }
}
