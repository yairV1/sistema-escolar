<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\Calificaciones\Models\Nota, tabla `detalle_notas`). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_notas', function (Blueprint $table) {
            $table->increments('id_nota');
            $table->unsignedInteger('id_actividad');
            $table->unsignedInteger('id_estudiante');
            $table->decimal('nota', 3, 1)->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20)->default('pendiente');

            $table->foreign('id_actividad')->references('id_actividad')->on('actividades')->cascadeOnDelete();
            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->cascadeOnDelete();

            $table->unique(['id_actividad', 'id_estudiante']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_notas');
    }
};
