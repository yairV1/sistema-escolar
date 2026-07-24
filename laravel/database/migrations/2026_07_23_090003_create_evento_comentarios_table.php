<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_comentarios', function (Blueprint $table) {
            $table->id('id_comentario');
            $table->unsignedBigInteger('id_evento');
            $table->unsignedInteger('id_usuario');
            $table->text('comentario');
            $table->timestamps();

            $table->foreign('id_evento')->references('id_evento')->on('eventos')->cascadeOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->restrictOnDelete();

            $table->index('id_evento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_comentarios');
    }
};
