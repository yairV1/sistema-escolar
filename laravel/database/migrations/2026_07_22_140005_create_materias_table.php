<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstruida desde el uso en código: tabla legacy sin migración propia
 * (App\Modules\GestionAcademica\Models\Materia). `id_area` NO se declara
 * aquí a propósito: la agrega database/migrations/2026_08_03_090000_create_areas_table.php,
 * que corre después y hace el ALTER + FK sobre esta tabla.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->increments('id_materia');
            $table->string('nombre_materia', 100)->unique();
            $table->string('descripcion', 1000)->nullable();
            $table->unsignedTinyInteger('intensidad_horaria');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
