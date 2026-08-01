<?php

namespace App\Modules\SuperAdmin\Policies;

use App\Modules\Auth\Models\Usuario;

class ConfiguracionPlataformaPolicy
{
    public function editar(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.configuracion.editar');
    }
}
