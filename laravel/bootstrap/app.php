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

        // Fase 1 (P1-4): proxies de confianza por entorno (TRUSTED_PROXIES),
        // nunca «todos»; reemplaza el TrustProxies por defecto de Laravel.
        $middleware->replace(
            \Illuminate\Http\Middleware\TrustProxies::class,
            \App\Core\Http\Middleware\ConfiarProxiesConfigurados::class,
        );

        // Fase 1 (P0-1/P1-4): cabecera Host limitada a los hosts del servidor
        // (APP_URL + APP_TRUSTED_HOSTS) fuera de local/testing. Global y después
        // de los proxies, para evaluar el host ya resuelto.
        $middleware->append(\App\Core\Http\Middleware\ValidarHostConfiable::class);

        $middleware->appendToGroup('web', [
            // Fase 1 (P1-1/P1-2): cuenta, rol e institución habilitados en cada
            // petición autenticada (sesión o cookie «recordarme»).
            \App\Core\Http\Middleware\EnsureCuentaHabilitada::class,
            // Cierra sesiones iniciadas antes de sesion_valida_desde (cambio de
            // contraseña, rol, permisos, desactivación o suspensión).
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
