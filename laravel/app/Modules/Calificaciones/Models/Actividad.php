<?php

namespace App\Modules\Calificaciones\Models;

use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;

class Actividad extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = 'asignacion.curso';

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
