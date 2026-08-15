<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\Observaciones\Models\Observacion). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observaciones', function (Blueprint $table) {
            $table->increments('id_observacion');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_profesor');
            $table->enum('tipo_observacion', ['academica', 'disciplinaria', 'convivencia', 'positiva']);
            $table->text('descripcion');
            $table->date('fecha');
            $table->enum('nivel_gravedad', ['baja', 'media', 'alta']);
            $table->enum('estado', ['activa', 'archivada'])->default('activa');

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->cascadeOnDelete();
            $table->foreign('id_profesor')->references('id_profesor')->on('profesores')->cascadeOnDelete();

            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observaciones');
    }
};
