<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\Usuarios\Models\Profesor). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesores', function (Blueprint $table) {
            $table->increments('id_profesor');
            $table->unsignedInteger('id_usuario')->unique();
            $table->string('codigo_profesor', 30)->unique();
            $table->string('profesion', 100)->nullable();
            $table->string('especialidad', 100)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->enum('estado_laboral', ['activo', 'licencia', 'retirado', 'vacaciones'])->default('activo');

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesores');
    }
};
