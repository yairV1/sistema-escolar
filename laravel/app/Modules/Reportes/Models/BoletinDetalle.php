<?php

namespace App\Modules\Reportes\Models;

use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoletinDetalle extends Model
{
    protected $table = 'boletin_detalle';

    protected $primaryKey = 'id_boletin_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_boletin',
        'id_asignacion',
        'nota_definitiva',
        'origen',
        'tipo_observacion',
        'observacion_materia',
    ];

    public const TIPOS_OBSERVACION = ['fortaleza', 'dificultad', 'recomendacion'];

    public const ORIGEN_CALCULADO = 'calculado';
    public const ORIGEN_MANUAL = 'manual';

    public function boletin(): BelongsTo
    {
        return $this->belongsTo(Boletin::class, 'id_boletin', 'id_boletin');
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionAcademica::class, 'id_asignacion', 'id_asignacion');
    }
}
