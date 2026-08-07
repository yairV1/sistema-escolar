<?php

namespace App\Modules\Matriculas\Models;

use App\Modules\Instituciones\Support\BelongsToInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudAdmision extends Model
{
    use BelongsToInstitucion;

    protected $table = 'solicitudes_admision';

    protected $primaryKey = 'id_solicitud';

    protected $fillable = [
        'id_institucion',
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
