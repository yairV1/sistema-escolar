<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\Reportes\Models\Boletin). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletines', function (Blueprint $table) {
            $table->increments('id_boletin');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_periodo');
            $table->decimal('promedio_general', 3, 2)->nullable();
            $table->unsignedSmallInteger('puesto_curso')->nullable();
            $table->text('observaciones_gral')->nullable();
            $table->enum('estado', ['borrador', 'publicado', 'anulado'])->default('borrador');

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->cascadeOnDelete();
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_academicos')->cascadeOnDelete();

            $table->unique(['id_estudiante', 'id_periodo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletines');
    }
};
