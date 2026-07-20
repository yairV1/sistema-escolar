<?php

namespace App\Modules\Calificaciones\Models;

use Illuminate\Database\Eloquent\Model;

class TipoActividad extends Model
{
    protected $table = 'tipos_actividad';

    protected $primaryKey = 'id_tipo_actividad';

    public $timestamps = false;

    protected $fillable = [
        'nombre_tipo',
        'descripcion',
        'estado',
    ];
}
