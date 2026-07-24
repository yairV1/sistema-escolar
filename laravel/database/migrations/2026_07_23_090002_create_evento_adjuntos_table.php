<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_adjuntos', function (Blueprint $table) {
            $table->id('id_adjunto');
            $table->unsignedBigInteger('id_evento');
            $table->unsignedInteger('id_usuario_subio');
            $table->string('nombre_original', 255);
            $table->string('ruta', 500);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('tamano_bytes');
            $table->timestamps();

            $table->foreign('id_evento')->references('id_evento')->on('eventos')->cascadeOnDelete();
            $table->foreign('id_usuario_subio')->references('id_usuario')->on('usuarios')->restrictOnDelete();

            $table->index('id_evento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_adjuntos');
    }
};
