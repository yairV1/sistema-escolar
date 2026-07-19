<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingNoticia extends Model
{
    protected $table = 'landing_noticias';

    protected $primaryKey = 'id_noticia';

    protected $fillable = [
        'titulo',
        'descripcion',
        'etiqueta',
        'imagen',
        'fecha',
        'orden',
        'estado',
    ];
}
