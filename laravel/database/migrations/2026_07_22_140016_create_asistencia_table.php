<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\Asistencia\Models\Asistencia). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia', function (Blueprint $table) {
            $table->increments('id_asistencia');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_asignacion');
            $table->date('fecha');
            $table->enum('estado_asistencia', ['presente', 'ausente', 'tarde', 'excusa']);
            $table->string('observacion', 300)->nullable();

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->cascadeOnDelete();
            $table->foreign('id_asignacion')->references('id_asignacion')->on('asignaciones_academicas')->cascadeOnDelete();

            $table->unique(['id_asignacion', 'id_estudiante', 'fecha'], 'asistencia_unica_por_dia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia');
    }
};
