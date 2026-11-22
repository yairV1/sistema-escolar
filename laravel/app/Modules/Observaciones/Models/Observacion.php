<?php

namespace App\Modules\Observaciones\Models;

use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;

class Observacion extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = 'estudiante.usuario';

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
