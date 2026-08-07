<?php

namespace App\Providers;

use App\Modules\Calendario\Services\CalendarioFeedService;
use App\Modules\Calendario\Services\Occurrences\ActividadOccurrenceSource;
use App\Modules\Calendario\Services\Occurrences\EventoOccurrenceSource;
use App\Modules\Calendario\Services\Occurrences\HorarioOccurrenceSource;
use App\Modules\Auditoria\Models\AuditLog;
use App\Modules\Auditoria\Policies\AuditLogPolicy;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Policies\InstitucionPolicy;
use App\Modules\Modulos\Models\Modulo;
use App\Modules\Modulos\Policies\ModuloPolicy;
use App\Modules\Planes\Models\Plan;
use App\Modules\Planes\Policies\PlanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // CalendarioFeedService recibe sus fuentes por array porque el
        // contenedor no puede inferir "todas las implementaciones de esta
        // interfaz" automáticamente; agregar una fuente nueva (Google
        // Calendar, Outlook...) es sumarla acá, nada más.
        $this->app->bind(CalendarioFeedService::class, fn ($app) => new CalendarioFeedService([
            $app->make(HorarioOccurrenceSource::class),
            $app->make(ActividadOccurrenceSource::class),
            $app->make(EventoOccurrenceSource::class),
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Nombre, logo y demás datos institucionales: antes quemados en cada
        // vista/mailable, ahora vienen todos de la fila única de configuración.
        View::composer(
            ['layouts.auth', 'layouts.panel', 'Rector.dashboard.index', 'website.index'],
            fn ($view) => $view->with('colegioConfiguracion', ColegioConfiguracion::singleton()),
        );

        // Primeras Policies reales del proyecto (Fase A de la plataforma
        // multi-tenant). Registradas explícitamente en vez de depender del
        // auto-descubrimiento por convención de Laravel, para que quede
        // claro en un único lugar qué Policy protege qué modelo.
        Gate::policy(Institucion::class, InstitucionPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Modulo::class, ModuloPolicy::class);

        // Rate limiting propio de SuperAdmin (throttle:superadmin), separado
        // de cualquier límite del panel institucional.
        RateLimiter::for('superadmin', fn ($request) => Limit::perMinute(60)->by(
            $request->user()?->id_usuario ?: $request->ip(),
        ));

        RateLimiter::for('2fa', fn ($request) => Limit::perMinute(6)->by($request->ip()));
    }
}
