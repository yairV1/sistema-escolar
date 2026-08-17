<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\GestionAcademica\Models\AsignacionAcademica). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_academicas', function (Blueprint $table) {
            $table->increments('id_asignacion');
            $table->unsignedInteger('id_profesor');
            $table->unsignedInteger('id_materia');
            $table->unsignedInteger('id_curso');
            $table->unsignedSmallInteger('anio_lectivo');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            $table->foreign('id_profesor')->references('id_profesor')->on('profesores')->cascadeOnDelete();
            $table->foreign('id_materia')->references('id_materia')->on('materias')->cascadeOnDelete();
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->cascadeOnDelete();

            $table->unique(['id_profesor', 'id_materia', 'id_curso', 'anio_lectivo'], 'asignaciones_unicas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_academicas');
    }
};
