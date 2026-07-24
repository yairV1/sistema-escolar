<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_historial', function (Blueprint $table) {
            $table->id('id_historial');
            $table->unsignedBigInteger('id_evento');
            $table->unsignedInteger('id_usuario');
            $table->enum('accion', ['creado', 'actualizado', 'estado_cambiado', 'desactivado', 'activado', 'movido']);
            $table->json('cambios')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_evento')->references('id_evento')->on('eventos')->cascadeOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->restrictOnDelete();

            $table->index(['id_evento', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_historial');
    }
};
