<?php

namespace App\Modules\GestionAcademica\Models;

use App\Modules\Usuarios\Models\Profesor;
use App\Modules\Instituciones\Support\CatalogoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    /** Etapa 2 · B5: catálogo institucional; sin plantillas (las filas sin institución son compartidas/pendientes, solo lectura). */
    use CatalogoInstitucional;

    public const PLANTILLA_GLOBAL = false;

    protected $table = 'materias';

    protected $primaryKey = 'id_materia';

    public $timestamps = false;

    protected $fillable = [
        'nombre_materia',
        'descripcion',
        'intensidad_horaria',
        'estado',
        'id_materia_padre',
    ];

    public function profesores(): BelongsToMany
    {
        return $this->belongsToMany(Profesor::class, 'profesor_materia', 'id_materia', 'id_profesor');
    }

    /** Solo un nivel de anidación: una sub-asignatura no puede a su vez tener padre (ver MateriaRequest). */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_materia_padre', 'id_materia');
    }

    public function subAsignaturas(): HasMany
    {
        return $this->hasMany(self::class, 'id_materia_padre', 'id_materia');
    }
}
