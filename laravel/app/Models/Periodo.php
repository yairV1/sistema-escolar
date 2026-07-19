<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
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
