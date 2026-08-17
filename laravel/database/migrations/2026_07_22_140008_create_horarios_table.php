<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\GestionAcademica\Models\Horario). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->increments('id_horario');
            $table->unsignedInteger('id_asignacion');
            $table->enum('dia_semana', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('salon', 50)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            $table->foreign('id_asignacion')->references('id_asignacion')->on('asignaciones_academicas')->cascadeOnDelete();

            $table->unique(['id_asignacion', 'dia_semana', 'hora_inicio'], 'horarios_bloque_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
