<?php

namespace App\Modules\Auditoria\Models;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Instituciones\Models\Institucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Bitácora append-only de acciones críticas de la plataforma. Sin updated_at, igual que EventoHistorial. */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $primaryKey = 'id_log';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_institucion',
        'accion',
        'entidad_tipo',
        'entidad_id',
        'datos_antes',
        'datos_despues',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'datos_antes' => 'array',
        'datos_despues' => 'array',
        'created_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }
}
