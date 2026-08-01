<?php

namespace App\Modules\Auditoria\Services;

use App\Modules\Auditoria\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Registra explícitamente una acción crítica desde el propio controlador
 * que la ejecuta (no vía Observer implícito): más fácil de auditar qué
 * acción escribe qué, como exige el espíritu de
 * docs/arquitectura/08-estandares.md §5.2 para Policies ("explícitas y
 * fáciles de auditar") aplicado aquí a la bitácora.
 */
class AuditLogger
{
    public function __construct(private Request $request) {}

    public function record(string $accion, ?Model $entidad = null, array $antes = [], array $despues = []): AuditLog
    {
        $usuario = Auth::user();

        return AuditLog::create([
            'id_usuario' => $usuario?->id_usuario,
            'id_institucion' => $usuario?->id_institucion,
            'accion' => $accion,
            'entidad_tipo' => $entidad ? $entidad::class : null,
            'entidad_id' => $entidad?->getKey(),
            'datos_antes' => $antes ?: null,
            'datos_despues' => $despues ?: null,
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}
