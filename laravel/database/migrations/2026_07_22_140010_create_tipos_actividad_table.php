<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\Calificaciones\Models\TipoActividad). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_actividad', function (Blueprint $table) {
            $table->increments('id_tipo_actividad');
            $table->string('nombre_tipo', 50)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_actividad');
    }
};
