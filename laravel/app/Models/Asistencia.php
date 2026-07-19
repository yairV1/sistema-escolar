<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    protected $table = 'asistencia';

    protected $primaryKey = 'id_asistencia';

    public $timestamps = false;

    public const ESTADOS = ['presente', 'ausente', 'tarde', 'excusa'];

    protected $fillable = [
        'id_estudiante',
        'id_asignacion',
        'fecha',
        'estado_asistencia',
        'observacion',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionAcademica::class, 'id_asignacion', 'id_asignacion');
    }
}
