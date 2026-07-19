<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingGaleria extends Model
{
    protected $table = 'landing_galeria';

    protected $primaryKey = 'id_foto';

    protected $fillable = [
        'imagen',
        'descripcion',
        'orden',
        'estado',
    ];
}
