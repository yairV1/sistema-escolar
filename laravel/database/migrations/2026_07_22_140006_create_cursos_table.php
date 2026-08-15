<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstruida desde el uso en código: tabla legacy sin migración propia
 * (App\Modules\GestionAcademica\Models\Curso). `id_institucion` NO se
 * declara aquí a propósito: la agrega
 * database/migrations/2026_07_26_090000_add_institucion_to_cursos_table.php,
 * que corre después.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->increments('id_curso');
            $table->string('nombre_curso', 20);
            $table->enum('nivel_academico', ['preescolar', 'primaria', 'secundaria', 'media']);
            $table->enum('jornada', ['manana', 'tarde', 'noche', 'unica']);
            $table->unsignedSmallInteger('anio_lectivo');
            $table->unsignedInteger('id_director_grupo')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            $table->foreign('id_director_grupo')->references('id_profesor')->on('profesores')->nullOnDelete();

            $table->unique(['nombre_curso', 'anio_lectivo', 'jornada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
