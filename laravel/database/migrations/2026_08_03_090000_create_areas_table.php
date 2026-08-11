<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrupa materias afines (ej: Ciencias Naturales agrupa Biología, Química,
 * Física) para reportes y organización académica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->increments('id_area');
            $table->string('nombre_area', 100)->unique();
            $table->string('descripcion', 1000)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->unsignedInteger('id_area')->nullable()->after('id_materia');

            $table->foreign('id_area')
                ->references('id_area')->on('areas')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropForeign(['id_area']);
            $table->dropColumn('id_area');
        });

        Schema::dropIfExists('areas');
    }
};
