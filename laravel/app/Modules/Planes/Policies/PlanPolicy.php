<?php

namespace App\Modules\Planes\Policies;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Planes\Models\Plan;

/**
 * Mismo patrón que InstitucionPolicy (docs/arquitectura/08-estandares.md
 * §5.2): el permiso ya filtró "puede este rol intentar esto"
 * (EnsurePermission), esta Policy es la segunda capa, explícita.
 */
class PlanPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.planes.ver');
    }

    public function view(Usuario $usuario, Plan $plan): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.planes.ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.planes.crear');
    }

    public function update(Usuario $usuario, Plan $plan): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.planes.editar');
    }

    public function activate(Usuario $usuario, Plan $plan): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.planes.activar');
    }

    public function deactivate(Usuario $usuario, Plan $plan): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.planes.desactivar');
    }
}
