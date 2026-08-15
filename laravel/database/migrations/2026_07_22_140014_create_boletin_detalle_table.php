<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstruida desde el uso en código: tabla legacy sin migración propia
 * (App\Modules\Reportes\Models\BoletinDetalle). `tipo_observacion` y
 * `origen` NO se declaran aquí a propósito: las agregan, en este orden,
 * database/migrations/2026_08_02_090000_add_tipo_observacion_to_boletin_detalle_table.php
 * y 2026_08_06_090000_add_origen_to_boletin_detalle_table.php (ambas
 * hacen `->after('nota_definitiva')`, por eso esa columna sí va aquí).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletin_detalle', function (Blueprint $table) {
            $table->increments('id_boletin_detalle');
            $table->unsignedInteger('id_boletin');
            $table->unsignedInteger('id_asignacion');
            $table->decimal('nota_definitiva', 3, 1)->nullable();
            $table->text('observacion_materia')->nullable();

            $table->foreign('id_boletin')->references('id_boletin')->on('boletines')->cascadeOnDelete();
            $table->foreign('id_asignacion')->references('id_asignacion')->on('asignaciones_academicas')->cascadeOnDelete();

            $table->unique(['id_boletin', 'id_asignacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletin_detalle');
    }
};
