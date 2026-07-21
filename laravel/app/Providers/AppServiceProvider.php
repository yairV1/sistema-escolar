<?php

namespace App\Providers;

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
        //
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
