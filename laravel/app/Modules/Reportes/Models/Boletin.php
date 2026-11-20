<?php

namespace App\Modules\Reportes\Models;

use App\Modules\Calificaciones\Models\Periodo;
use App\Modules\Usuarios\Models\Estudiante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;

class Boletin extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = 'estudiante.usuario';

    protected $table = 'boletines';

    protected $primaryKey = 'id_boletin';

    public $timestamps = false;

    public const ESTADOS = ['borrador', 'publicado', 'anulado'];

    protected $fillable = [
        'id_estudiante',
        'id_periodo',
        'promedio_general',
        'puesto_curso',
        'observaciones_gral',
        'estado',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }

    public function detalle(): HasMany
    {
        return $this->hasMany(BoletinDetalle::class, 'id_boletin', 'id_boletin');
    }
}
