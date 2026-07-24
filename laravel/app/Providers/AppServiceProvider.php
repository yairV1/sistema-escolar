<?php

namespace App\Providers;

use App\Modules\Calendario\Services\CalendarioFeedService;
use App\Modules\Calendario\Services\Occurrences\ActividadOccurrenceSource;
use App\Modules\Calendario\Services\Occurrences\EventoOccurrenceSource;
use App\Modules\Calendario\Services\Occurrences\HorarioOccurrenceSource;
use App\Modules\Colegio\Models\ColegioConfiguracion;
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
    }
}
