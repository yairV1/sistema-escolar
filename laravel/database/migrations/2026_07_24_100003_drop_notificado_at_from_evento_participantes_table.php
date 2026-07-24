<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `notificado_at` (Fase 3) asumía un solo envío por evento+usuario. No
 * alcanza para recordatorios reales: un evento recurrente tiene varias
 * ocurrencias, y un evento puede tener varios offsets configurados a la vez
 * — ninguno de los dos ejes cabe en una sola columna timestamp. Se
 * reemplaza por evento_recordatorios_enviados (Fase 5), que sí modela
 * ambos ejes con una clave compuesta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evento_participantes', function (Blueprint $table) {
            $table->dropColumn('notificado_at');
        });
    }

    public function down(): void
    {
        Schema::table('evento_participantes', function (Blueprint $table) {
            $table->timestamp('notificado_at')->nullable();
        });
    }
};
