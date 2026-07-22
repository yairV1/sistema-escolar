<?php

namespace App\Modules\Matriculas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudAdmision extends Model
{
    protected $table = 'solicitudes_admision';

    protected $primaryKey = 'id_solicitud';

    protected $fillable = [
        'nombre_acudiente',
        'apellido_acudiente',
        'correo',
        'telefono',
        'nombre_estudiante',
        'grado_interes',
        'mensaje',
        'estado',
        'id_matricula',
    ];

    public const ESTADOS = ['pendiente', 'contactada', 'convertida', 'descartada'];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }
}
