<?php

namespace App\Modules\SuperAdmin\Policies;

use App\Modules\Auth\Models\Usuario;

/**
 * No se registra vía Gate::policy(Usuario::class, ...) a propósito: el
 * modelo Usuario ya está reservado para la futura CuentaPolicy oficial de
 * docs/arquitectura/03-rbac.md §5 (Fase 3, todavía pendiente) — enlazarla
 * aquí colisionaría con ese diseño ya aprobado. Esta Policy se invoca
 * manualmente desde UsuarioGlobalController, no mediante $this->authorize().
 */
class UsuarioGlobalPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.usuarios_globales.ver');
    }

    public function cambiarRol(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.usuarios_globales.gestionar');
    }

    public function cambiarEstado(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.usuarios_globales.gestionar');
    }
}
