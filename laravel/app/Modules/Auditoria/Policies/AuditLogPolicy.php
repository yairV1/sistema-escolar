<?php

namespace App\Modules\Auditoria\Policies;

use App\Modules\Auditoria\Models\AuditLog;
use App\Modules\Auth\Models\Usuario;

class AuditLogPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.auditoria.ver');
    }

    public function view(Usuario $usuario, AuditLog $log): bool
    {
        return $usuario->esSuperAdmin() && $usuario->hasPermission('plataforma.auditoria.ver');
    }
}
