<?php

namespace App\Modules\Calificaciones\Models;

use Illuminate\Database\Eloquent\Model;

/** Escala de desempeño (Bajo/Básico/Alto/Superior) configurable por el colegio,
 *  usada para traducir la nota definitiva numérica a una sigla en el boletín. */
class EscalaNota extends Model
{
    protected $table = 'escala_notas';

    protected $primaryKey = 'id_escala';

    protected $fillable = [
        'etiqueta',
        'sigla',
        'valor_min',
        'valor_max',
        'orden',
    ];
}
