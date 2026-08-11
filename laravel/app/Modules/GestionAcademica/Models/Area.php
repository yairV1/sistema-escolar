<?php

namespace App\Modules\GestionAcademica\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $table = 'areas';

    protected $primaryKey = 'id_area';

    public $timestamps = false;

    protected $fillable = [
        'nombre_area',
        'descripcion',
        'estado',
    ];

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'id_area', 'id_area');
    }
}
