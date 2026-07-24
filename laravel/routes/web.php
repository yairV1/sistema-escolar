<?php

use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\PasswordResetController;
use App\Modules\Auth\Controllers\RolController;
use App\Modules\Calendario\Controllers\CalendarioController;
use App\Modules\Calendario\Controllers\CalendarioExportController;
use App\Modules\Calendario\Controllers\EventoAdjuntoController;
use App\Modules\Calendario\Controllers\EventoCategoriaController;
use App\Modules\Calendario\Controllers\EventoComentarioController;
use App\Modules\Calendario\Controllers\EventoController;
use App\Modules\Calendario\Controllers\NotificacionPanelController;
use App\Modules\Calificaciones\Controllers\CalificacionesController;
use App\Modules\Colegio\Controllers\ConfiguracionColegioController;
use App\Modules\Comunicados\Controllers\ComunicadosController;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Docente\Controllers\DocenteController;
use App\Modules\GestionAcademica\Controllers\GestionAcademicaController;
use App\Modules\Landing\Controllers\EditarLandingController;
use App\Modules\Landing\Controllers\SolicitudAdmisionController;
use App\Modules\Landing\Controllers\WebsiteController;
use App\Modules\Matriculas\Controllers\MatriculasController;
use App\Modules\Observaciones\Controllers\ObservacionesController;
use App\Modules\Perfil\Controllers\PerfilController;
use App\Modules\Rector\Controllers\AsistenciaController;
use App\Modules\Reportes\Controllers\BoletinesController;
use App\Modules\Reportes\Controllers\EstadisticasController;
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

Route::middleware(['auth', 'role:admin,rector'])->prefix('listados')->group(function () {
    Route::get('/', [ListadosController::class, 'index'])->name('listados');
    Route::post('/estudiantes/{estudiante}/desactivar', [ListadosController::class, 'desactivarEstudiante'])->name('listados.estudiantes.desactivar');
    Route::post('/estudiantes/{estudiante}/activar', [ListadosController::class, 'activarEstudiante'])->name('listados.estudiantes.activar');
    Route::post('/docentes/{profesor}/desactivar', [ListadosController::class, 'desactivarDocente'])->name('listados.docentes.desactivar');
    Route::post('/docentes/{profesor}/activar', [ListadosController::class, 'activarDocente'])->name('listados.docentes.activar');
    Route::post('/administrativos/{usuario}/desactivar', [ListadosController::class, 'desactivarAdministrativo'])->name('listados.administrativos.desactivar');
    Route::post('/administrativos/{usuario}/activar', [ListadosController::class, 'activarAdministrativo'])->name('listados.administrativos.activar');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('matriculas')->group(function () {
    Route::get('/', [MatriculasController::class, 'index'])->name('matriculas');
    Route::post('/{matricula}/estado', [MatriculasController::class, 'cambiarEstado'])->name('matriculas.estado');
    Route::post('/solicitudes/{solicitud}/estado', [MatriculasController::class, 'cambiarEstadoSolicitud'])->name('matriculas.solicitudes.estado');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('gestion-academica')->name('gestion-academica.')->group(function () {
    Route::get('/', [GestionAcademicaController::class, 'index'])->name('index');

    Route::post('/materias', [GestionAcademicaController::class, 'storeMateria'])->name('materias.store');
    Route::post('/materias/{materia}', [GestionAcademicaController::class, 'updateMateria'])->name('materias.update');
    Route::post('/materias/{materia}/desactivar', [GestionAcademicaController::class, 'desactivarMateria'])->name('materias.desactivar');
    Route::post('/materias/{materia}/activar', [GestionAcademicaController::class, 'activarMateria'])->name('materias.activar');

    Route::get('/cursos/{curso}', [GestionAcademicaController::class, 'show'])->name('cursos.show');
    Route::post('/cursos', [GestionAcademicaController::class, 'storeCurso'])->name('cursos.store');
    Route::post('/cursos/{curso}', [GestionAcademicaController::class, 'updateCurso'])->name('cursos.update');
    Route::post('/cursos/{curso}/desactivar', [GestionAcademicaController::class, 'desactivarCurso'])->name('cursos.desactivar');
    Route::post('/cursos/{curso}/activar', [GestionAcademicaController::class, 'activarCurso'])->name('cursos.activar');

    Route::post('/asignaciones', [GestionAcademicaController::class, 'storeAsignacion'])->name('asignaciones.store');
    Route::post('/asignaciones/{asignacion}/desactivar', [GestionAcademicaController::class, 'desactivarAsignacion'])->name('asignaciones.desactivar');
    Route::post('/asignaciones/{asignacion}/activar', [GestionAcademicaController::class, 'activarAsignacion'])->name('asignaciones.activar');

    Route::post('/horarios', [GestionAcademicaController::class, 'storeHorario'])->name('horarios.store');
    Route::post('/horarios/{horario}', [GestionAcademicaController::class, 'updateHorario'])->name('horarios.update');
    Route::post('/horarios/{horario}/desactivar', [GestionAcademicaController::class, 'desactivarHorario'])->name('horarios.desactivar');
    Route::post('/horarios/{horario}/activar', [GestionAcademicaController::class, 'activarHorario'])->name('horarios.activar');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('registro')->name('registro.')->group(function () {
    Route::get('/estudiantes', [RegistroEstudiantesController::class, 'create'])->name('estudiantes.create');
    Route::post('/estudiantes', [RegistroEstudiantesController::class, 'store'])->name('estudiantes.store');
    Route::get('/estudiantes/{estudiante}/editar', [RegistroEstudiantesController::class, 'edit'])->name('estudiantes.edit');
    Route::post('/estudiantes/{estudiante}', [RegistroEstudiantesController::class, 'update'])->name('estudiantes.update');

    Route::get('/docentes', [RegistroDocentesController::class, 'create'])->name('docentes.create');
    Route::post('/docentes', [RegistroDocentesController::class, 'store'])->name('docentes.store');
    Route::get('/docentes/{profesor}/editar', [RegistroDocentesController::class, 'edit'])->name('docentes.edit');
    Route::post('/docentes/{profesor}', [RegistroDocentesController::class, 'update'])->name('docentes.update');

    Route::get('/administrativos', [RegistroAdministrativosController::class, 'create'])->name('administrativos.create');
    Route::post('/administrativos', [RegistroAdministrativosController::class, 'store'])->name('administrativos.store');
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
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('observaciones')->name('observaciones.')->group(function () {
    Route::get('/', [ObservacionesController::class, 'index'])->name('index');
    Route::post('/', [ObservacionesController::class, 'store'])->name('store');
    Route::post('/{observacion}', [ObservacionesController::class, 'update'])->name('update');
    Route::post('/{observacion}/desactivar', [ObservacionesController::class, 'desactivar'])->name('desactivar');
    Route::post('/{observacion}/activar', [ObservacionesController::class, 'activar'])->name('activar');
});

Route::middleware(['auth', 'role:admin,rector'])->prefix('boletines')->name('boletines.')->group(function () {
    Route::get('/', [BoletinesController::class, 'index'])->name('index');
    Route::post('/generar', [BoletinesController::class, 'generar'])->name('generar');
    Route::get('/{boletin}', [BoletinesController::class, 'show'])->name('show');
    Route::post('/{boletin}/publicar', [BoletinesController::class, 'publicar'])->name('publicar');
    Route::post('/{boletin}/anular', [BoletinesController::class, 'anular'])->name('anular');
    Route::post('/{boletin}/borrador', [BoletinesController::class, 'volverBorrador'])->name('borrador');
});

Route::middleware(['auth', 'role:admin,rector,docente'])->prefix('asistencia')->name('asistencia.')->group(function () {
    Route::get('/asignaciones/{asignacion}', [AsistenciaController::class, 'show'])->name('show');
    Route::post('/asignaciones/{asignacion}', [AsistenciaController::class, 'guardar'])->name('guardar');
    Route::get('/asignaciones/{asignacion}/historial', [AsistenciaController::class, 'historial'])->name('historial');
});

Route::get('/estadisticas', [EstadisticasController::class, 'index'])
    ->middleware(['auth', 'role:admin,rector'])
    ->name('estadisticas');

Route::middleware(['auth', 'role:admin,rector'])->prefix('comunicados')->name('comunicados.')->group(function () {
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
