<?php

namespace App\Modules\Reportes\Models;

use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;

class BoletinDetalle extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = 'boletin.estudiante.usuario';

    protected $table = 'boletin_detalle';

    protected $primaryKey = 'id_boletin_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_boletin',
        'id_asignacion',
        'nota_definitiva',
        'observacion_materia',
    ];

    public function boletin(): BelongsTo
    {
        return $this->belongsTo(Boletin::class, 'id_boletin', 'id_boletin');
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionAcademica::class, 'id_asignacion', 'id_asignacion');
    }
}
