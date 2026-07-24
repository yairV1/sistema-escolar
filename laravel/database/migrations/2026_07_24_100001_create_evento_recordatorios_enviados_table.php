<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_recordatorios_enviados', function (Blueprint $table) {
            $table->id('id_recordatorio_enviado');
            $table->unsignedBigInteger('id_evento');
            $table->unsignedInteger('id_usuario');
            $table->date('fecha_ocurrencia');
            $table->unsignedInteger('minutos_antes');
            $table->timestamp('enviado_at')->useCurrent();

            $table->foreign('id_evento')->references('id_evento')->on('eventos')->cascadeOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();

            $table->unique(['id_evento', 'id_usuario', 'fecha_ocurrencia', 'minutos_antes'], 'evt_recordatorios_enviados_unicos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_recordatorios_enviados');
    }
};
