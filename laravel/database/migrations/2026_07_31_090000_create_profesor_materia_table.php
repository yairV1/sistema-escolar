<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivote profesor <-> materia: qué materias puede dictar un docente,
 * independiente del curso/año lectivo (eso sigue viviendo en
 * asignaciones_academicas, gestionado desde Gestión Académica). Permite
 * fijar la(s) materia(s) desde el propio registro del docente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesor_materia', function (Blueprint $table) {
            $table->unsignedInteger('id_profesor');
            $table->unsignedInteger('id_materia');

            $table->primary(['id_profesor', 'id_materia']);

            $table->foreign('id_profesor')
                ->references('id_profesor')->on('profesores')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('id_materia')
                ->references('id_materia')->on('materias')
                ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesor_materia');
    }
};
