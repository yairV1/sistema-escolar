<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El docente de la materia deja, al cerrar el periodo, una observación
 * clasificada (fortaleza/dificultad/recomendación) en vez de que el
 * boletín solo muestre su nombre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletin_detalle', function (Blueprint $table) {
            $table->enum('tipo_observacion', ['fortaleza', 'dificultad', 'recomendacion'])
                ->nullable()
                ->after('nota_definitiva');
        });
    }

    public function down(): void
    {
        Schema::table('boletin_detalle', function (Blueprint $table) {
            $table->dropColumn('tipo_observacion');
        });
    }
};
