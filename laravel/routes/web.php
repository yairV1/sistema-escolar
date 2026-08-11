<?php

use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\PasswordResetController;
use App\Modules\Auth\Controllers\TwoFactorChallengeController;
use App\Modules\Acudiente\Controllers\AcudienteController;
use App\Modules\Auditoria\Controllers\AuditoriaController;
use App\Modules\Auth\Controllers\RolController;
use App\Modules\Calendario\Controllers\CalendarioController;
use App\Modules\Calendario\Controllers\CalendarioExportController;
use App\Modules\Calendario\Controllers\EventoAdjuntoController;
use App\Modules\Calendario\Controllers\EventoCategoriaController;
use App\Modules\Calendario\Controllers\EventoComentarioController;
use App\Modules\Calendario\Controllers\EventoController;
use App\Modules\Calendario\Controllers\NotificacionPanelController;
use App\Modules\Calificaciones\Controllers\CalificacionesController;
use App\Modules\Calificaciones\Controllers\EscalaNotasController;
use App\Modules\Colegio\Controllers\ConfiguracionColegioController;
use App\Modules\Comunicados\Controllers\ComunicadosController;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Docente\Controllers\DocenteController;
use App\Modules\Estudiante\Controllers\EstudianteController;
use App\Modules\GestionAcademica\Controllers\AsignacionesController;
use App\Modules\GestionAcademica\Controllers\CursosController;
use App\Modules\GestionAcademica\Controllers\HorariosController;
use App\Modules\GestionAcademica\Controllers\MateriasController;
use App\Modules\Landing\Controllers\EditarLandingController;
use App\Modules\Landing\Controllers\SolicitudAdmisionController;
use App\Modules\Landing\Controllers\WebsiteController;
use App\Modules\Matriculas\Controllers\MatriculasController;
use App\Modules\Observaciones\Controllers\ObservacionesController;
use App\Modules\Perfil\Controllers\PerfilController;
use App\Modules\Rector\Controllers\AsistenciaController;
use App\Modules\Reportes\Controllers\BoletinesController;
use App\Modules\Reportes\Controllers\DirectorGrupoController;
use App\Modules\Reportes\Controllers\EstadisticasController;
use App\Modules\Soporte\Controllers\SoporteController;
use App\Modules\SuperAdmin\Controllers\ConfiguracionPlataformaController;
use App\Modules\SuperAdmin\Controllers\InstitucionController;
use App\Modules\SuperAdmin\Controllers\ModuloController;
use App\Modules\SuperAdmin\Controllers\PlanController;
use App\Modules\SuperAdmin\Controllers\RolPermisoController;
use App\Modules\SuperAdmin\Controllers\SuperAdminDashboardController;
use App\Modules\SuperAdmin\Controllers\TwoFactorController as SuperAdminTwoFactorController;
use App\Modules\SuperAdmin\Controllers\UsuarioGlobalController;
use App\Modules\Usuarios\Controllers\ListadosController;
use App\Modules\Usuarios\Controllers\Registro\RegistroAdministrativosController;
use App\Modules\Usuarios\Controllers\Registro\RegistroDocentesController;
use App\Modules\Usuarios\Controllers\Registro\RegistroEstudiantesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'index'])->name('home');

Route::post('/solicitudes-admision', [SolicitudAdmisionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('solicitudes-admision.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::post('/forgot-password', [PasswordResetController::class, 'storeForgot'])->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'storeReset'])->name('password.update');

    // Segundo paso del login para cuentas con 2FA confirmado — el usuario
    // aún no está autenticado (Auth::user() es null) hasta que el código se
    // verifica, ver TwoFactorChallengeController.
    Route::prefix('2fa')->name('2fa.')->group(function () {
        Route::get('/', [TwoFactorChallengeController::class, 'show'])->name('challenge.show');
        Route::post('/', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:2fa')->name('challenge.store');
    });
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/inicio', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin,rector'])
    ->name('inicio');

Route::get('/mi-panel', [DocenteController::class, 'dashboard'])
    ->middleware(['auth', 'role:docente'])
    ->name('docente.dashboard');

Route::middleware(['auth', 'role:docente'])->prefix('docente')->name('docente.')->group(function () {
    Route::get('/estudiantes', [DocenteController::class, 'estudiantes'])->name('estudiantes');
    Route::get('/asistencia', [DocenteController::class, 'asistencia'])->name('asistencia');
    Route::get('/calificaciones', [DocenteController::class, 'calificaciones'])->name('calificaciones');
    Route::get('/horario', [DocenteController::class, 'horario'])->name('horario')->middleware('modulo:horarios');
    Route::get('/comunicados', [DocenteController::class, 'comunicados'])->name('comunicados')->middleware('modulo:comunicados');
    Route::post('/comunicados/{notificacion}/leido', [DocenteController::class, 'marcarComunicadoLeido'])->name('comunicados.leido')->middleware('modulo:comunicados');
});

Route::middleware(['auth', 'role:estudiante'])->prefix('estudiante')->name('estudiante.')->group(function () {
    Route::get('/inicio', [EstudianteController::class, 'inicio'])->name('inicio');
    Route::get('/horario', [EstudianteController::class, 'horario'])->name('horario')->middleware('modulo:horarios');
    Route::get('/materias', [EstudianteController::class, 'materias'])->name('materias');
    Route::get('/notas', [EstudianteController::class, 'notas'])->name('notas')->middleware('modulo:boletines');
    Route::get('/asistencia', [EstudianteController::class, 'asistencia'])->name('asistencia');
    Route::get('/comunicados', [EstudianteController::class, 'comunicados'])->name('comunicados')->middleware('modulo:comunicados');
    Route::get('/perfil', [EstudianteController::class, 'perfil'])->name('perfil');
});

Route::middleware(['auth', 'role:acudiente'])->prefix('acudiente')->name('acudiente.')->group(function () {
    Route::get('/inicio', [AcudienteController::class, 'inicio'])->name('inicio');
    Route::get('/estudiantes', [AcudienteController::class, 'estudiantes'])->name('estudiantes');
    Route::get('/horario', [AcudienteController::class, 'horario'])->name('horario')->middleware('modulo:horarios');
    Route::get('/notas', [AcudienteController::class, 'notas'])->name('notas')->middleware('modulo:boletines');
    Route::get('/asistencia', [AcudienteController::class, 'asistencia'])->name('asistencia');
    Route::get('/comunicados', [AcudienteController::class, 'comunicados'])->name('comunicados')->middleware('modulo:comunicados');
    Route::get('/perfil', [AcudienteController::class, 'perfil'])->name('perfil');
});

Route::middleware('auth')->group(function () {
    Route::get('/soporte', [SoporteController::class, 'create'])->name('soporte.create');
    Route::post('/soporte', [SoporteController::class, 'store'])->name('soporte.store');
});

// role:admin,superadmin (no solo role:superadmin): la bandeja de soporte no
// tiene un permiso plataforma.* propio porque es infraestructura compartida
// con el admin técnico institucional, no una capacidad exclusiva de
// SuperAdmin — ver docs/arquitectura/10-superadmin-plataforma.md.
Route::middleware(['auth', 'role:admin,superadmin'])->prefix('soportes')->name('soportes.')->group(function () {
    Route::get('/', [SoporteController::class, 'index'])->name('index');
    Route::get('/{soporte}', [SoporteController::class, 'show'])->name('show');
    Route::post('/{soporte}/resolver', [SoporteController::class, 'resolver'])->name('resolver');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('listados')->group(function () {
    Route::get('/', [ListadosController::class, 'index'])->name('listados');
    Route::post('/estudiantes/{estudiante}/desactivar', [ListadosController::class, 'desactivarEstudiante'])->name('listados.estudiantes.desactivar');
    Route::post('/estudiantes/{estudiante}/activar', [ListadosController::class, 'activarEstudiante'])->name('listados.estudiantes.activar');
    Route::post('/docentes/{profesor}/desactivar', [ListadosController::class, 'desactivarDocente'])->name('listados.docentes.desactivar');
    Route::post('/docentes/{profesor}/activar', [ListadosController::class, 'activarDocente'])->name('listados.docentes.activar');
    Route::post('/administrativos/{usuario}/desactivar', [ListadosController::class, 'desactivarAdministrativo'])->name('listados.administrativos.desactivar');
    Route::post('/administrativos/{usuario}/activar', [ListadosController::class, 'activarAdministrativo'])->name('listados.administrativos.activar');
});

Route::middleware(['auth', 'role:admin,rector', 'modulo:matriculas'])->prefix('matriculas')->group(function () {
    Route::get('/', [MatriculasController::class, 'index'])->name('matriculas');
    Route::post('/{matricula}/estado', [MatriculasController::class, 'cambiarEstado'])->name('matriculas.estado');
    Route::post('/solicitudes/{solicitud}/estado', [MatriculasController::class, 'cambiarEstadoSolicitud'])->name('matriculas.solicitudes.estado');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('gestion-academica')->name('gestion-academica.')->group(function () {
    Route::prefix('materias')->name('materias.')->group(function () {
        Route::get('/', [MateriasController::class, 'index'])->name('index');
        Route::post('/', [MateriasController::class, 'store'])->name('store');

        // Debe registrarse antes de '/{materia}' para que "areas" no sea
        // capturado como un id de materia.
        Route::prefix('areas')->name('areas.')->group(function () {
            Route::post('/', [MateriasController::class, 'storeArea'])->name('store');
            Route::post('/{area}', [MateriasController::class, 'updateArea'])->name('update');
            Route::post('/{area}/desactivar', [MateriasController::class, 'desactivarArea'])->name('desactivar');
            Route::post('/{area}/activar', [MateriasController::class, 'activarArea'])->name('activar');
        });

        Route::post('/{materia}', [MateriasController::class, 'update'])->name('update');
        Route::post('/{materia}/desactivar', [MateriasController::class, 'desactivar'])->name('desactivar');
        Route::post('/{materia}/activar', [MateriasController::class, 'activar'])->name('activar');
    });

    Route::prefix('cursos')->name('cursos.')->group(function () {
        Route::get('/', [CursosController::class, 'index'])->name('index');
        Route::get('/{curso}', [CursosController::class, 'show'])->name('show');
        Route::post('/', [CursosController::class, 'store'])->name('store');
        Route::post('/{curso}', [CursosController::class, 'update'])->name('update');
        Route::post('/{curso}/desactivar', [CursosController::class, 'desactivar'])->name('desactivar');
        Route::post('/{curso}/activar', [CursosController::class, 'activar'])->name('activar');
    });

    Route::prefix('asignaciones')->name('asignaciones.')->group(function () {
        Route::get('/', [AsignacionesController::class, 'index'])->name('index');
        Route::post('/', [AsignacionesController::class, 'store'])->name('store');
        Route::post('/{asignacion}/desactivar', [AsignacionesController::class, 'desactivar'])->name('desactivar');
        Route::post('/{asignacion}/activar', [AsignacionesController::class, 'activar'])->name('activar');
    });

    Route::middleware('modulo:horarios')->prefix('horarios')->name('horarios.')->group(function () {
        Route::get('/', [HorariosController::class, 'index'])->name('index');
        Route::post('/', [HorariosController::class, 'store'])->name('store');
        Route::post('/{horario}', [HorariosController::class, 'update'])->name('update');
        Route::post('/{horario}/desactivar', [HorariosController::class, 'desactivar'])->name('desactivar');
        Route::post('/{horario}/activar', [HorariosController::class, 'activar'])->name('activar');
    });
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('registro')->name('registro.')->group(function () {
    Route::get('/estudiantes', [RegistroEstudiantesController::class, 'create'])->name('estudiantes.create');
    Route::post('/estudiantes', [RegistroEstudiantesController::class, 'store'])->name('estudiantes.store');
    Route::get('/estudiantes/{estudiante}', [RegistroEstudiantesController::class, 'show'])->name('estudiantes.show');
    Route::get('/estudiantes/{estudiante}/editar', [RegistroEstudiantesController::class, 'edit'])->name('estudiantes.edit');
    Route::post('/estudiantes/{estudiante}', [RegistroEstudiantesController::class, 'update'])->name('estudiantes.update');

    Route::get('/docentes', [RegistroDocentesController::class, 'create'])->name('docentes.create');
    Route::post('/docentes', [RegistroDocentesController::class, 'store'])->name('docentes.store');
    Route::get('/docentes/{profesor}', [RegistroDocentesController::class, 'show'])->name('docentes.show');
    Route::get('/docentes/{profesor}/editar', [RegistroDocentesController::class, 'edit'])->name('docentes.edit');
    Route::post('/docentes/{profesor}', [RegistroDocentesController::class, 'update'])->name('docentes.update');

    Route::get('/administrativos', [RegistroAdministrativosController::class, 'create'])->name('administrativos.create');
    Route::post('/administrativos', [RegistroAdministrativosController::class, 'store'])->name('administrativos.store');
    Route::get('/administrativos/{usuario}', [RegistroAdministrativosController::class, 'show'])->name('administrativos.show');
    Route::get('/administrativos/{usuario}/editar', [RegistroAdministrativosController::class, 'edit'])->name('administrativos.edit');
    Route::post('/administrativos/{usuario}', [RegistroAdministrativosController::class, 'update'])->name('administrativos.update');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('calificaciones')->name('calificaciones.')->group(function () {
    Route::get('/', [CalificacionesController::class, 'index'])->name('index');

    Route::post('/periodos', [CalificacionesController::class, 'storePeriodo'])->name('periodos.store');
    Route::post('/periodos/{periodo}', [CalificacionesController::class, 'updatePeriodo'])->name('periodos.update');
    Route::post('/periodos/{periodo}/desactivar', [CalificacionesController::class, 'desactivarPeriodo'])->name('periodos.desactivar');
    Route::post('/periodos/{periodo}/activar', [CalificacionesController::class, 'activarPeriodo'])->name('periodos.activar');

    Route::post('/tipos-actividad', [CalificacionesController::class, 'storeTipoActividad'])->name('tipos-actividad.store');
    Route::post('/tipos-actividad/{tipoActividad}', [CalificacionesController::class, 'updateTipoActividad'])->name('tipos-actividad.update');
    Route::post('/tipos-actividad/{tipoActividad}/desactivar', [CalificacionesController::class, 'desactivarTipoActividad'])->name('tipos-actividad.desactivar');
    Route::post('/tipos-actividad/{tipoActividad}/activar', [CalificacionesController::class, 'activarTipoActividad'])->name('tipos-actividad.activar');

    Route::post('/escala-notas', [EscalaNotasController::class, 'guardar'])->name('escala-notas.guardar');
});

// Rutas de calificaciones acotadas a una asignación puntual: además de admin/rector,
// las puede usar el profesor dueño de esa asignación (ver Usuario::puedeGestionarAsignacion).
Route::middleware(['auth', 'role:admin,rector,docente'])->prefix('calificaciones')->name('calificaciones.')->group(function () {
    Route::get('/asignaciones/{asignacion}', [CalificacionesController::class, 'asignacion'])->name('asignaciones.show');
    Route::post('/asignaciones/{asignacion}/actividades', [CalificacionesController::class, 'storeActividad'])->name('actividades.store');
    Route::post('/actividades/{actividad}', [CalificacionesController::class, 'updateActividad'])->name('actividades.update');
    Route::post('/actividades/{actividad}/desactivar', [CalificacionesController::class, 'desactivarActividad'])->name('actividades.desactivar');
    Route::post('/actividades/{actividad}/activar', [CalificacionesController::class, 'activarActividad'])->name('actividades.activar');

    Route::get('/actividades/{actividad}/notas', [CalificacionesController::class, 'notas'])->name('actividades.notas');
    Route::post('/actividades/{actividad}/notas', [CalificacionesController::class, 'guardarNotas'])->name('actividades.notas.guardar');

    Route::post('/asignaciones/{asignacion}/observaciones', [CalificacionesController::class, 'guardarObservaciones'])->name('asignaciones.observaciones.guardar');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('observaciones')->name('observaciones.')->group(function () {
    Route::get('/', [ObservacionesController::class, 'index'])->name('index');
    Route::post('/', [ObservacionesController::class, 'store'])->name('store');
    Route::post('/{observacion}', [ObservacionesController::class, 'update'])->name('update');
    Route::post('/{observacion}/desactivar', [ObservacionesController::class, 'desactivar'])->name('desactivar');
    Route::post('/{observacion}/activar', [ObservacionesController::class, 'activar'])->name('activar');
});

Route::middleware(['auth', 'role:admin,rector', 'modulo:boletines'])->prefix('boletines')->name('boletines.')->group(function () {
    Route::get('/', [BoletinesController::class, 'index'])->name('index');
    Route::post('/generar', [BoletinesController::class, 'generar'])->name('generar');
    Route::get('/pdf-masivo', [BoletinesController::class, 'pdfMasivo'])->name('pdf-masivo');
    Route::get('/{boletin}', [BoletinesController::class, 'show'])->name('show');
    Route::get('/{boletin}/pdf', [BoletinesController::class, 'pdf'])->name('pdf');
    Route::post('/{boletin}/publicar', [BoletinesController::class, 'publicar'])->name('publicar');
    Route::post('/{boletin}/anular', [BoletinesController::class, 'anular'])->name('anular');
    Route::post('/{boletin}/borrador', [BoletinesController::class, 'volverBorrador'])->name('borrador');
});

// El director de grupo (Curso::id_director_grupo) digita la nota definitiva directamente;
// admin/rector también pueden entrar para dar soporte. Autorización fina en el controlador
// (Usuario::esDirectorDeGrupo) porque depende del curso puntual, no solo del rol.
Route::middleware(['auth', 'role:admin,rector,docente', 'modulo:boletines'])->prefix('director-grupo')->name('director-grupo.')->group(function () {
    Route::get('/', [DirectorGrupoController::class, 'index'])->name('index');
    Route::get('/{curso}/{periodo}', [DirectorGrupoController::class, 'notas'])->name('notas');
    Route::post('/{curso}/{periodo}', [DirectorGrupoController::class, 'guardar'])->name('guardar');
});

Route::middleware(['auth', 'role:admin,rector,docente'])->prefix('asistencia')->name('asistencia.')->group(function () {
    Route::get('/asignaciones/{asignacion}', [AsistenciaController::class, 'show'])->name('show');
    Route::post('/asignaciones/{asignacion}', [AsistenciaController::class, 'guardar'])->name('guardar');
    Route::get('/asignaciones/{asignacion}/historial', [AsistenciaController::class, 'historial'])->name('historial');
});

Route::get('/estadisticas', [EstadisticasController::class, 'index'])
    ->middleware(['auth', 'role:admin,rector'])
    ->name('estadisticas');

Route::middleware(['auth', 'role:admin,rector', 'modulo:comunicados'])->prefix('comunicados')->name('comunicados.')->group(function () {
    Route::get('/', [ComunicadosController::class, 'index'])->name('index');
    Route::post('/', [ComunicadosController::class, 'store'])->name('store');
});

Route::middleware('auth')->prefix('perfil')->name('perfil.')->group(function () {
    Route::get('/', [PerfilController::class, 'show'])->name('show');
    Route::post('/', [PerfilController::class, 'update'])->name('update');
    Route::post('/password', [PerfilController::class, 'updatePassword'])->name('password');
});

// Panel-wide, no una acción de calendario — hoy Calendario es el único
// productor de Notification, ver NotificacionPanelController.
Route::middleware('auth')->prefix('notificaciones')->name('notificaciones.')->group(function () {
    Route::get('/', [NotificacionPanelController::class, 'index'])->name('index');
    Route::post('/leer-todas', [NotificacionPanelController::class, 'leerTodas'])->name('leer-todas');
});

// Suscripción .ics sin sesión (Google/Outlook la consultan periódicamente
// sin cookies) — el token de 64 caracteres es la única protección, ver
// CalendarioExportController::suscripcion(). Registrada antes del grupo
// con el wildcard /calendario/{evento} por el mismo motivo que
// calendario/categorias arriba, aunque en este caso no hay colisión real
// (dos segmentos extra vs. uno solo).
Route::get('/calendario/ics/{usuario}/{token}', [CalendarioExportController::class, 'suscripcion'])->name('calendario.ics');

// Admin-only: gestión de categorías (color/ícono/permisos/orden) — decisión
// institucional, no una acción de calendario personal. Registrada ANTES del
// grupo con el wildcard /calendario/{evento}: si fuera al revés, una
// petición a /calendario/categorias resolvería {evento}="categorias" en
// vez de llegar acá (Laravel matchea rutas en orden de registro).
Route::middleware(['auth', 'role:admin,rector'])->prefix('calendario/categorias')->name('calendario.categorias.')->group(function () {
    Route::get('/', [EventoCategoriaController::class, 'index'])->name('index');
    Route::post('/', [EventoCategoriaController::class, 'store'])->name('store');
    Route::post('/{categoria}', [EventoCategoriaController::class, 'update'])->name('update');
    Route::post('/{categoria}/desactivar', [EventoCategoriaController::class, 'desactivar'])->name('desactivar');
    Route::post('/{categoria}/activar', [EventoCategoriaController::class, 'activar'])->name('activar');
});

// Sin restricción de rol: cada rol ve/edita un subconjunto distinto por
// scoping de datos y Policy (VisibilidadCalendarioService, EventoPolicy),
// no por acceso a la ruta.
Route::middleware('auth')->prefix('calendario')->name('calendario.')->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('index');
    Route::get('/feed', [CalendarioController::class, 'feed'])->name('feed');

    // Registradas antes del wildcard /{evento}: si fueran después, GET
    // /calendario/exportar resolvería {evento}="exportar" en vez de llegar
    // acá (mismo motivo que calendario/categorias más arriba).
    Route::get('/exportar', [CalendarioExportController::class, 'descargar'])->name('exportar');
    Route::post('/exportar/token', [CalendarioExportController::class, 'generarToken'])->name('exportar.token');

    Route::post('/', [EventoController::class, 'store'])->name('store');
    Route::get('/{evento}', [EventoController::class, 'show'])->name('show');
    Route::post('/{evento}', [EventoController::class, 'update'])->name('update');
    Route::post('/{evento}/mover', [EventoController::class, 'mover'])->name('mover');
    Route::post('/{evento}/duplicar', [EventoController::class, 'duplicar'])->name('duplicar');
    Route::post('/{evento}/estado', [EventoController::class, 'cambiarEstado'])->name('estado');
    Route::post('/{evento}/desactivar', [EventoController::class, 'desactivar'])->name('desactivar');
    Route::post('/{evento}/activar', [EventoController::class, 'activar'])->name('activar');

    Route::post('/{evento}/adjuntos', [EventoAdjuntoController::class, 'store'])->name('adjuntos.store');
    Route::post('/adjuntos/{adjunto}/eliminar', [EventoAdjuntoController::class, 'destroy'])->name('adjuntos.destroy');

    Route::post('/{evento}/comentarios', [EventoComentarioController::class, 'store'])->name('comentarios.store');
    Route::post('/comentarios/{comentario}/eliminar', [EventoComentarioController::class, 'destroy'])->name('comentarios.destroy');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('editar-landing')->name('editar-landing.')->group(function () {
    Route::get('/', [EditarLandingController::class, 'index'])->name('index');
    Route::post('/contenido', [EditarLandingController::class, 'updateContenido'])->name('contenido.update');
    Route::post('/hero-imagen', [EditarLandingController::class, 'updateHeroImagen'])->name('hero-imagen.update');

    Route::post('/noticias', [EditarLandingController::class, 'storeNoticia'])->name('noticias.store');
    Route::post('/noticias/{noticia}', [EditarLandingController::class, 'updateNoticia'])->name('noticias.update');
    Route::post('/noticias/{noticia}/desactivar', [EditarLandingController::class, 'desactivarNoticia'])->name('noticias.desactivar');
    Route::post('/noticias/{noticia}/activar', [EditarLandingController::class, 'activarNoticia'])->name('noticias.activar');

    Route::post('/galeria', [EditarLandingController::class, 'storeGaleria'])->name('galeria.store');
    Route::post('/galeria/{galeria}', [EditarLandingController::class, 'updateGaleria'])->name('galeria.update');
    Route::post('/galeria/{galeria}/desactivar', [EditarLandingController::class, 'desactivarGaleria'])->name('galeria.desactivar');
    Route::post('/galeria/{galeria}/activar', [EditarLandingController::class, 'activarGaleria'])->name('galeria.activar');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('configuracion-colegio')->name('configuracion-colegio.')->group(function () {
    Route::get('/', [ConfiguracionColegioController::class, 'index'])->name('index');
    Route::post('/', [ConfiguracionColegioController::class, 'update'])->name('update');

    Route::post('/imagenes', [ConfiguracionColegioController::class, 'storeImagen'])->name('imagenes.store');
    Route::post('/imagenes/{imagen}/eliminar', [ConfiguracionColegioController::class, 'destroyImagen'])->name('imagenes.eliminar');
    Route::post('/imagenes/reordenar', [ConfiguracionColegioController::class, 'reordenarImagenes'])->name('imagenes.reordenar');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('roles')->name('roles.')->group(function () {
    Route::get('/', [RolController::class, 'index'])->name('index');
    Route::post('/{rol}', [RolController::class, 'update'])->name('update');
    Route::post('/{rol}/desactivar', [RolController::class, 'desactivar'])->name('desactivar');
    Route::post('/{rol}/activar', [RolController::class, 'activar'])->name('activar');
});

// =====================================================================
// SUPERADMIN — plataforma multi-tenant (docs/arquitectura/10-superadmin-plataforma.md)
// Middleware propio (`superadmin`), nunca `role:...`: capa de autorización
// separada del panel institucional a propósito. `permission:...` es la
// primera implementación real de PermissionMiddleware (03-rbac.md §7.2),
// una entrada por permiso del catálogo `plataforma.*`.
// =====================================================================
Route::middleware(['auth', 'superadmin', 'throttle:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->middleware('permission:plataforma.metricas.ver')->name('dashboard');

    Route::prefix('instituciones')->name('instituciones.')->group(function () {
        Route::get('/', [InstitucionController::class, 'index'])->middleware('permission:plataforma.instituciones.ver')->name('index');
        Route::get('/crear', [InstitucionController::class, 'create'])->middleware('permission:plataforma.instituciones.crear')->name('create');
        Route::post('/', [InstitucionController::class, 'store'])->middleware('permission:plataforma.instituciones.crear')->name('store');
        Route::get('/{institucion}', [InstitucionController::class, 'show'])->middleware('permission:plataforma.instituciones.ver')->name('show');
        Route::get('/{institucion}/editar', [InstitucionController::class, 'edit'])->middleware('permission:plataforma.instituciones.editar')->name('edit');
        Route::post('/{institucion}', [InstitucionController::class, 'update'])->middleware('permission:plataforma.instituciones.editar')->name('update');
        Route::post('/{institucion}/activar', [InstitucionController::class, 'activar'])->middleware('permission:plataforma.instituciones.activar')->name('activar');
        Route::post('/{institucion}/desactivar', [InstitucionController::class, 'desactivar'])->middleware('permission:plataforma.instituciones.desactivar')->name('desactivar');
    });

    // Catálogo comercial: planes y módulos (docs/arquitectura/10-superadmin-plataforma.md,
    // sección "Catálogo de planes y módulos"). No es facturación real —
    // no hay pasarela de pago en el proyecto — es metadata estructurada
    // que reemplaza el string libre `instituciones.plan`.
    Route::prefix('planes')->name('planes.')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->middleware('permission:plataforma.planes.ver')->name('index');
        Route::get('/crear', [PlanController::class, 'create'])->middleware('permission:plataforma.planes.crear')->name('create');
        Route::post('/', [PlanController::class, 'store'])->middleware('permission:plataforma.planes.crear')->name('store');
        Route::get('/{plan}/editar', [PlanController::class, 'edit'])->middleware('permission:plataforma.planes.editar')->name('edit');
        Route::post('/{plan}', [PlanController::class, 'update'])->middleware('permission:plataforma.planes.editar')->name('update');
        Route::post('/{plan}/duplicar', [PlanController::class, 'duplicar'])->middleware('permission:plataforma.planes.crear')->name('duplicar');
        Route::post('/{plan}/activar', [PlanController::class, 'activar'])->middleware('permission:plataforma.planes.activar')->name('activar');
        Route::post('/{plan}/desactivar', [PlanController::class, 'desactivar'])->middleware('permission:plataforma.planes.desactivar')->name('desactivar');
    });

    Route::prefix('modulos')->name('modulos.')->group(function () {
        Route::get('/', [ModuloController::class, 'index'])->middleware('permission:plataforma.modulos.ver')->name('index');
        Route::get('/crear', [ModuloController::class, 'create'])->middleware('permission:plataforma.modulos.crear')->name('create');
        Route::post('/', [ModuloController::class, 'store'])->middleware('permission:plataforma.modulos.crear')->name('store');
        Route::get('/{modulo}/editar', [ModuloController::class, 'edit'])->middleware('permission:plataforma.modulos.editar')->name('edit');
        Route::post('/{modulo}', [ModuloController::class, 'update'])->middleware('permission:plataforma.modulos.editar')->name('update');
        Route::post('/{modulo}/activar', [ModuloController::class, 'activar'])->middleware('permission:plataforma.modulos.activar')->name('activar');
        Route::post('/{modulo}/desactivar', [ModuloController::class, 'desactivar'])->middleware('permission:plataforma.modulos.desactivar')->name('desactivar');
    });

    Route::middleware('permission:plataforma.usuarios_globales.ver')->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioGlobalController::class, 'index'])->name('index');
        Route::post('/{usuario}/rol', [UsuarioGlobalController::class, 'cambiarRol'])->name('rol');
        Route::post('/{usuario}/estado', [UsuarioGlobalController::class, 'cambiarEstado'])->name('estado');
    });

    Route::middleware('permission:plataforma.roles.gestionar')->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolPermisoController::class, 'index'])->name('index');
        Route::post('/', [RolPermisoController::class, 'update'])->name('update');
        Route::post('/{rol}/desactivar', [RolPermisoController::class, 'desactivar'])->name('desactivar');
        Route::post('/{rol}/activar', [RolPermisoController::class, 'activar'])->name('activar');
    });

    Route::middleware('permission:plataforma.auditoria.ver')->prefix('auditoria')->name('auditoria.')->group(function () {
        Route::get('/', [AuditoriaController::class, 'index'])->name('index');
    });

    Route::middleware('permission:plataforma.configuracion.editar')->prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('/', [ConfiguracionPlataformaController::class, 'index'])->name('index');
        Route::post('/', [ConfiguracionPlataformaController::class, 'update'])->name('update');
    });

    // Autoservicio de 2FA de la propia cuenta SuperAdmin — sin permiso
    // adicional, todo actor autenticado gestiona su propio segundo factor.
    Route::prefix('2fa')->name('2fa.')->group(function () {
        Route::get('/', [SuperAdminTwoFactorController::class, 'index'])->name('index');
        Route::post('/habilitar', [SuperAdminTwoFactorController::class, 'enable'])->name('enable');
        Route::post('/confirmar', [SuperAdminTwoFactorController::class, 'confirm'])->name('confirm');
        Route::post('/deshabilitar', [SuperAdminTwoFactorController::class, 'disable'])->name('disable');
        Route::post('/regenerar-codigos', [SuperAdminTwoFactorController::class, 'regenerarCodigosRecuperacion'])->name('regenerar-codigos');
    });
});
