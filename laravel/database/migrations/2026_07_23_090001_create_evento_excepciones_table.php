<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_excepciones', function (Blueprint $table) {
            $table->id('id_excepcion');
            $table->unsignedBigInteger('id_evento');
            $table->date('fecha_original');
            $table->enum('tipo', ['cancelada', 'movida']);
            $table->date('nueva_fecha_inicio')->nullable();
            $table->time('nueva_hora_inicio')->nullable();
            $table->date('nueva_fecha_fin')->nullable();
            $table->time('nueva_hora_fin')->nullable();
            $table->timestamps();

            $table->foreign('id_evento')->references('id_evento')->on('eventos')->cascadeOnDelete();

            $table->unique(['id_evento', 'fecha_original']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_excepciones');
    }
};
