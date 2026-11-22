<?php

namespace App\Modules\Calificaciones\Models;

use App\Modules\Usuarios\Models\Estudiante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;

class Nota extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = 'actividad.asignacion.curso';

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
