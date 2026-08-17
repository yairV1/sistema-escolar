<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstruida desde el uso en código: tabla legacy sin migración propia
 * (App\Modules\Calificaciones\Models\Periodo). Ver database/migrations/2026_08_07_140000_create_password_resets_table.php
 * para el mismo tipo de brecha ya resuelta en `password_resets`/`usuarios`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->increments('id_periodo');
            $table->string('nombre_periodo', 50);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->unsignedSmallInteger('anio_lectivo');
            $table->enum('estado', ['pendiente', 'activo', 'cerrado'])->default('pendiente');

            $table->unique(['nombre_periodo', 'anio_lectivo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_academicos');
    }
};
