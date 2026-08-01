<?php

namespace App\Modules\Instituciones\Policies;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Instituciones\Models\Institucion;

/**
 * Autorización a nivel de recurso (docs/arquitectura/08-estandares.md
 * §5.2, Policies) sobre Institucion. El permiso ya filtró "puede este rol
 * intentar esto" (EnsurePermission); esta Policy es la segunda capa que
 * exige el propio 03-rbac.md §2 — hoy es siempre institucional (solo
 * SuperAdmin llega aquí), sin alcance por registro, pero se declara
 * explícita en vez de resolverse solo en el middleware.
 */
class InstitucionPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.instituciones.ver');
    }

    public function view(Usuario $usuario, Institucion $institucion): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.instituciones.ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.instituciones.crear');
    }

    public function update(Usuario $usuario, Institucion $institucion): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.instituciones.editar');
    }

    public function activate(Usuario $usuario, Institucion $institucion): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.instituciones.activar');
    }

    public function deactivate(Usuario $usuario, Institucion $institucion): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.instituciones.desactivar');
    }
}
