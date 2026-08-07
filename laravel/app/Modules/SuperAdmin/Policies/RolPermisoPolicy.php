<?php

namespace App\Modules\SuperAdmin\Policies;

use App\Modules\Auth\Models\Usuario;

/** Invocada manualmente desde RolPermisoController (no hay un modelo Eloquent único que represente "la matriz"). */
class RolPermisoPolicy
{
    public function gestionar(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.roles.gestionar');
    }
}
