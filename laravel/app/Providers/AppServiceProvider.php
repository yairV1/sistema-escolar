<?php

namespace App\Providers;

use App\Core\Seguridad\GuardiaBaseDePruebas;
use App\Modules\Calendario\Services\CalendarioFeedService;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Support\Facades\DB;
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
        // Etapa 2 · B5: modo legado/expandido por tabla, una instancia por petición
        $this->app->scoped(\App\Modules\Instituciones\Support\EsquemaInstitucional::class);

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
        // Comandos destructivos (migrate:fresh/refresh/reset, db:wipe) solo
        // contra la base de pruebas autorizada, verificada sobre la conexión
        // efectiva (GuardiaBaseDePruebas). migrate:rollback queda permitido:
        // es la vía de reversión de las migraciones de expansión.
        if ($this->app->runningInConsole()) {
            $prohibir = ! GuardiaBaseDePruebas::esAutorizada(DB::connection());
            FreshCommand::prohibit($prohibir);
            RefreshCommand::prohibit($prohibir);
            ResetCommand::prohibit($prohibir);
            WipeCommand::prohibit($prohibir);
        }

        // Etapa 2 · B6/B7: configuración del colegio de la SESIÓN (SuperAdmin → marca
        // neutra); sin sesión, la marca NEUTRA de la plataforma (login general). Si el
        // controlador ya fijó una (sitio /sitio/{slug}, login institucional), se respeta.
        View::composer(
            ['layouts.auth', 'layouts.panel', 'layouts.rector', 'layouts.docente', 'Rector.dashboard.index', 'website.index'],
            function ($view) {
                if (! array_key_exists('colegioConfiguracion', $view->getData())) {
                    $view->with('colegioConfiguracion', auth()->check() ? ColegioConfiguracion::actual() : ColegioConfiguracion::neutra());
                }
            },
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

        // Etapa 2 · B7: admisiones públicas por colegio — por IP y sitio, por minuto y por día.
        RateLimiter::for('admisiones', fn ($request) => [
            Limit::perMinute(5)->by('admision:'.$request->ip().'|'.$request->route('slug')),
            Limit::perDay(30)->by('admision-dia:'.$request->ip().'|'.$request->route('slug')),
        ]);

        // Freno a la fuerza bruta en el login: 5 intentos por minuto por
        // cuenta+IP (frena el ataque a una cuenta concreta) y 20 por IP
        // (frena el barrido de muchas cuentas desde un mismo origen).
        RateLimiter::for('login', function ($request) {
            $respuesta = fn () => response()->json([
                'message' => 'Demasiados intentos de inicio de sesión. Espera un minuto e inténtalo de nuevo.',
            ], 429);

            return [
                Limit::perMinute(5)->by(mb_strtolower(trim((string) $request->input('usuario'))).'|'.$request->ip())->response($respuesta),
                Limit::perMinute(20)->by($request->ip())->response($respuesta),
            ];
        });
    }
}
