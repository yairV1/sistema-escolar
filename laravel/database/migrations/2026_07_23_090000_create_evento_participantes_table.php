<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_participantes', function (Blueprint $table) {
            $table->id('id_participante');
            $table->unsignedBigInteger('id_evento');
            $table->unsignedInteger('id_usuario');
            $table->enum('rol_participacion', ['organizador', 'invitado'])->default('invitado');
            $table->enum('respuesta', ['pendiente', 'aceptado', 'rechazado'])->default('pendiente');
            $table->timestamp('notificado_at')->nullable();
            $table->timestamps();

            $table->foreign('id_evento')->references('id_evento')->on('eventos')->cascadeOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();

            $table->unique(['id_evento', 'id_usuario']);
            $table->index('id_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_participantes');
    }
};
