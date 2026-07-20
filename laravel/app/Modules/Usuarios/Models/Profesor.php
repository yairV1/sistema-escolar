<?php

namespace App\Modules\Usuarios\Models;

use App\Modules\Auth\Models\Usuario;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesor extends Model
{
    protected $table = 'profesores';

    protected $primaryKey = 'id_profesor';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'codigo_profesor',
        'profesion',
        'especialidad',
        'fecha_ingreso',
        'estado_laboral',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionAcademica::class, 'id_profesor', 'id_profesor');
    }
}
