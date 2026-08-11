<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El director de grupo puede digitar la nota definitiva directamente (sin pasar
 * actividad por actividad) una vez cerrado el periodo. `origen` distingue esa
 * nota manual de la calculada por promedio ponderado, para que
 * BoletinesController::generar() no la sobreescriba en una regeneración masiva.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletin_detalle', function (Blueprint $table) {
            $table->enum('origen', ['calculado', 'manual'])
                ->default('calculado')
                ->after('nota_definitiva');
        });
    }

    public function down(): void
    {
        Schema::table('boletin_detalle', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
