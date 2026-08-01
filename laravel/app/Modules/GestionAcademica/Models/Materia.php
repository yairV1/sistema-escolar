<?php

namespace App\Modules\GestionAcademica\Models;

use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Materia extends Model
{
    protected $table = 'materias';

    protected $primaryKey = 'id_materia';

    public $timestamps = false;

    protected $fillable = [
        'nombre_materia',
        'descripcion',
        'intensidad_horaria',
        'estado',
    ];

    public function profesores(): BelongsToMany
    {
        return $this->belongsToMany(Profesor::class, 'profesor_materia', 'id_materia', 'id_profesor');
    }
}
