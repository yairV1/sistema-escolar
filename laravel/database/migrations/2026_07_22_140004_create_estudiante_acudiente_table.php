<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla puente estudiante<->acudiente, manipulada con DB::table() crudo
 * desde App\Modules\Usuarios\Models\Acudiente::vincularEstudiante() (no
 * tiene modelo Eloquent propio). Reconstruida desde ese uso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante_acudiente', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_acudiente');
            $table->enum('parentesco', ['madre', 'padre', 'abuelo', 'tio', 'hermano', 'tutor_legal', 'otro']);
            $table->boolean('es_principal')->default(false);
            $table->string('estado', 20)->default('activo');
            $table->timestamp('fecha_registro')->useCurrent();

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->cascadeOnDelete();
            $table->foreign('id_acudiente')->references('id_acudiente')->on('acudientes')->cascadeOnDelete();

            $table->unique(['id_estudiante', 'id_acudiente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante_acudiente');
    }
};
