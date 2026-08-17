<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\Matriculas\Models\Matricula). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->increments('id_matricula');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_curso');
            $table->unsignedSmallInteger('anio_lectivo');
            $table->date('fecha_matricula');
            $table->enum('estado_matricula', ['activa', 'pendiente', 'retirada', 'cancelada'])->default('activa');
            $table->string('observacion', 255)->nullable();

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->cascadeOnDelete();
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->cascadeOnDelete();

            $table->unique(['id_estudiante', 'anio_lectivo']);
            $table->index(['id_curso', 'estado_matricula']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
