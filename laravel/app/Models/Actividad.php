<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Actividad extends Model
{
    protected $table = 'actividades';

    protected $primaryKey = 'id_actividad';

    public $timestamps = false;

    protected $fillable = [
        'id_asignacion',
        'id_periodo',
        'id_tipo_actividad',
        'titulo',
        'descripcion',
        'porcentaje',
        'fecha_entrega',
        'estado',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionAcademica::class, 'id_asignacion', 'id_asignacion');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoActividad::class, 'id_tipo_actividad', 'id_tipo_actividad');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'id_actividad', 'id_actividad');
    }
}
