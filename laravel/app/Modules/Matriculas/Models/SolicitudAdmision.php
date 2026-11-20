<?php

namespace App\Modules\Matriculas\Models;

use App\Modules\Instituciones\Support\BelongsToInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;

class SolicitudAdmision extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = '';

    use BelongsToInstitucion;

    protected $table = 'solicitudes_admision';

    protected $primaryKey = 'id_solicitud';

    // Etapa 2 · B7: id_institucion NO es asignable masivamente; lo fija el servidor.
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
