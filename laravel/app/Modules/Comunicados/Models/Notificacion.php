<?php

namespace App\Modules\Comunicados\Models;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;

class Notificacion extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = 'destino';

    protected $table = 'notificaciones';

    protected $primaryKey = 'id_notificacion';

    public $timestamps = false;

    public const TIPOS = ['informativa', 'academica', 'disciplinaria', 'pago', 'sistema'];

    public const TIPOS_LABELS = [
        'informativa' => 'Informativa',
        'academica' => 'Académica',
        'disciplinaria' => 'Disciplinaria',
        'pago' => 'Pago',
        'sistema' => 'Sistema',
    ];

    public const CANALES = ['interno', 'correo', 'whatsapp', 'todos'];

    protected $fillable = [
        'titulo',
        'mensaje',
        'id_usuario_origen',
        'id_usuario_destino',
        'tipo_notificacion',
        'canal',
        'fecha_envio',
        'leida',
        'fecha_lectura',
        'estado',
    ];

    public function origen(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_origen', 'id_usuario');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_destino', 'id_usuario');
    }
}
