<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soportes', function (Blueprint $table) {
            $table->id('id_soporte');
            $table->unsignedInteger('id_usuario');
            $table->string('asunto', 150);
            $table->text('mensaje');
            $table->enum('estado', ['nuevo', 'leido', 'resuelto'])->default('nuevo');
            $table->unsignedInteger('resuelto_por')->nullable();
            $table->timestamp('resuelto_at')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('resuelto_por')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soportes');
    }
};
