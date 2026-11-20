<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Auth\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Único punto por el que la aplicación cambia la contraseña de un usuario
 * (perfil y recuperación). Corrección del incidente del 2026-11-19
 * (docs/seguridad/incidente-superadmin-20261119.md):
 *
 *  - sesion_valida_desde = ahora → EnsureSessionFresh cierra TODAS las sesiones
 *    autenticadas antes del cambio en su siguiente petición;
 *  - remember_token rotado → ninguna cookie «recordarme» anterior vuelve a entrar;
 *  - audit_logs: «usuario.cambiar_password» con el origen, NUNCA la contraseña ni el hash.
 *
 * La sesión desde la que se hace el cambio (perfil) la conserva el controlador
 * renovando su marca auth_at.
 */
class CambioDeContrasena
{
    public const ORIGEN_PERFIL = 'perfil';

    public const ORIGEN_RECUPERACION = 'recuperacion';

    public function __construct(private AuditLogger $auditoria) {}

    public function aplicar(Usuario $usuario, string $nueva, string $origen): void
    {
        DB::transaction(function () use ($usuario, $nueva, $origen) {
            $usuario->forceFill([
                'password' => Hash::make($nueva),
                'sesion_valida_desde' => now(),
                'remember_token' => Str::random(60),
            ])->save();

            $this->auditoria->record('usuario.cambiar_password', $usuario, [], [
                'origen' => $origen,
                'sesiones_invalidadas' => true,
            ]);
        });
    }
}
