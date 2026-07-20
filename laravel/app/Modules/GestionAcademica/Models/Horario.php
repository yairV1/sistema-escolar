<?php

namespace App\Modules\GestionAcademica\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    protected $table = 'horarios';

    protected $primaryKey = 'id_horario';

    public $timestamps = false;

    public const DIAS_SEMANA = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

    protected $fillable = [
        'id_asignacion',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'salon',
        'estado',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionAcademica::class, 'id_asignacion', 'id_asignacion');
    }
}
