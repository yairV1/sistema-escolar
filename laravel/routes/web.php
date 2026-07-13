<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\GestionAcademicaController;
use App\Http\Controllers\Panel\ListadosController;
use App\Http\Controllers\Panel\MatriculasController;
use App\Http\Controllers\Panel\Registro\RegistroAdministrativosController;
use App\Http\Controllers\Panel\Registro\RegistroDocentesController;
use App\Http\Controllers\Panel\Registro\RegistroEstudiantesController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

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
