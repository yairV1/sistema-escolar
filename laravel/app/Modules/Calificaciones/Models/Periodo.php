<?php

namespace App\Modules\Calificaciones\Models;

use App\Modules\Instituciones\Support\CatalogoInstitucional;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    /** Etapa 2 · B5: catálogo institucional; sin plantillas (las filas sin institución son compartidas/pendientes, solo lectura). */
    use CatalogoInstitucional;

    public const PLANTILLA_GLOBAL = false;

    protected $table = 'periodos_academicos';

    protected $primaryKey = 'id_periodo';

    public $timestamps = false;

    public const ESTADOS = ['pendiente', 'activo', 'cerrado'];

    protected $fillable = [
        'nombre_periodo',
        'fecha_inicio',
        'fecha_fin',
        'anio_lectivo',
        'estado',
    ];
}
