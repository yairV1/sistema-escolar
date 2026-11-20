<?php

namespace App\Modules\Calificaciones\Models;

use App\Modules\Instituciones\Support\CatalogoInstitucional;
use Illuminate\Database\Eloquent\Model;

class TipoActividad extends Model
{
    /** Etapa 2 · B5: catálogo institucional; sin plantillas (las filas sin institución son compartidas/pendientes, solo lectura). */
    use CatalogoInstitucional;

    public const PLANTILLA_GLOBAL = false;

    protected $table = 'tipos_actividad';

    protected $primaryKey = 'id_tipo_actividad';

    public $timestamps = false;

    protected $fillable = [
        'nombre_tipo',
        'descripcion',
        'estado',
    ];
}
