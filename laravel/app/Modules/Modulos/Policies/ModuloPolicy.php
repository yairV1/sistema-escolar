<?php

namespace App\Modules\Modulos\Policies;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Modulos\Models\Modulo;

class ModuloPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.modulos.ver');
    }

    public function view(Usuario $usuario, Modulo $modulo): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.modulos.ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.modulos.crear');
    }

    public function update(Usuario $usuario, Modulo $modulo): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.modulos.editar');
    }

    public function activate(Usuario $usuario, Modulo $modulo): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.modulos.activar');
    }

    public function deactivate(Usuario $usuario, Modulo $modulo): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.modulos.desactivar');
    }
}
