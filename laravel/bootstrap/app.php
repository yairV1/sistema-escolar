<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withCommands([
        \App\Modules\Calendario\Console\Commands\EnviarRecordatoriosEventos::class,
        \App\Modules\Instituciones\Console\Commands\AuditarPertenenciaCommand::class,
        \App\Modules\Instituciones\Console\Commands\PoblarPertenenciaCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Core\Http\Middleware\EnsureRole::class,
            'superadmin' => \App\Core\Http\Middleware\EnsureSuperAdmin::class,
            'permission' => \App\Core\Http\Middleware\EnsurePermission::class,
        ]);

        // No-op para los 7 roles institucionales existentes (ver
        // EnsureSessionFresh): solo actúa si sesion_valida_desde está
        // poblado, lo que hoy únicamente ocurre cuando un SuperAdmin cambia
        // el rol o los permisos de alguien.
        $middleware->appendToGroup('web', [
            \App\Core\Http\Middleware\EnsureSessionFresh::class,
            \App\Core\Http\Middleware\SetLocale::class,
            // Etapa 1: todo modelo institucional de la URL debe ser de la
            // institución del usuario (404 si no). Va después de
            // SubstituteBindings (ya incluido en "web").
            \App\Core\Http\Middleware\EnsureRecursosDeInstitucion::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
