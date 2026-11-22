<?php

namespace App\Modules\Calendario\Models;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;

class EventoAdjunto extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = 'evento.creador';

    protected $table = 'evento_adjuntos';

    protected $primaryKey = 'id_adjunto';

    protected $fillable = [
        'id_evento',
        'id_usuario_subio',
        'nombre_original',
        'ruta',
        'mime_type',
        'tamano_bytes',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'id_evento', 'id_evento');
    }

    public function usuarioSubio(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_subio', 'id_usuario');
    }
}
