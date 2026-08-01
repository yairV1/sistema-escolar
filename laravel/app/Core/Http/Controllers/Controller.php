<?php

namespace App\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * `AuthorizesRequests` habilita $this->authorize(...) — sin uso hasta la
 * Fase A de la plataforma multi-tenant (docs/arquitectura/10-superadmin-plataforma.md),
 * primer consumidor real de Policies en este proyecto (InstitucionPolicy,
 * AuditLogPolicy, ver Gate::policy en AppServiceProvider).
 */
abstract class Controller
{
    use AuthorizesRequests;
}
