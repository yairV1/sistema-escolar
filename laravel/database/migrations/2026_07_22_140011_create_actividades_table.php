<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstruida desde el uso en código: tabla legacy sin migración propia
 * (App\Modules\Calificaciones\Models\Actividad). `estado` queda como
 * string libre (no enum) porque el código usa valores inconsistentes
 * ('activa'/'anulada' en CalificacionesController, 'activo' en
 * ActividadOccurrenceSource) — un enum estricto rompería una de las dos rutas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->increments('id_actividad');
            $table->unsignedInteger('id_asignacion');
            $table->unsignedInteger('id_periodo');
            $table->unsignedInteger('id_tipo_actividad');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('porcentaje', 5, 2);
            $table->date('fecha_entrega')->nullable();
            $table->string('estado', 20)->default('activa');
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->foreign('id_asignacion')->references('id_asignacion')->on('asignaciones_academicas')->cascadeOnDelete();
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_academicos')->cascadeOnDelete();
            $table->foreign('id_tipo_actividad')->references('id_tipo_actividad')->on('tipos_actividad')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
