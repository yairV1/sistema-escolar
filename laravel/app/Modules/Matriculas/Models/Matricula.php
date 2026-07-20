<?php

namespace App\Modules\Matriculas\Models;

use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Usuarios\Models\Estudiante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Matricula extends Model
{
    protected $table = 'matriculas';

    protected $primaryKey = 'id_matricula';

    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'id_curso',
        'anio_lectivo',
        'fecha_matricula',
        'estado_matricula',
        'observacion',
    ];

    public const ESTADOS = ['activa', 'pendiente', 'retirada', 'cancelada'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }
}
