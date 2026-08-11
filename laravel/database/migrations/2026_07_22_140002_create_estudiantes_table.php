<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\Usuarios\Models\Estudiante). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->increments('id_estudiante');
            $table->unsignedInteger('id_usuario')->unique();
            $table->string('codigo_estudiante', 30)->unique();
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['M', 'F', 'Otro']);
            $table->string('direccion', 200);
            $table->string('eps_seguro', 100)->nullable();
            $table->enum('estado_academico', ['activo', 'inactivo'])->default('activo');
            $table->date('fecha_ingreso');
            $table->text('observaciones_gral')->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
