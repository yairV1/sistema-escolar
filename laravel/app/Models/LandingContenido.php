<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingContenido extends Model
{
    protected $table = 'landing_contenido';

    protected $primaryKey = 'id_contenido';

    public $timestamps = false;

    protected $fillable = [
        'clave',
        'valor',
        'actualizado_en',
    ];
}
