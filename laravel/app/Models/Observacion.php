<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Observacion extends Model
{
    protected $table = 'observaciones';

    protected $primaryKey = 'id_observacion';

    public $timestamps = false;

    public const TIPOS = ['academica', 'disciplinaria', 'convivencia', 'positiva'];

    public const NIVELES = ['baja', 'media', 'alta'];

    protected $fillable = [
        'id_estudiante',
        'id_profesor',
        'tipo_observacion',
        'descripcion',
        'fecha',
        'nivel_gravedad',
        'estado',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }
}
