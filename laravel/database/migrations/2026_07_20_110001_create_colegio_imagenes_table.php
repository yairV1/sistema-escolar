<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colegio_imagenes', function (Blueprint $table) {
            $table->id('id_imagen');
            $table->foreignId('id_configuracion')
                ->constrained('colegio_configuracion', 'id_configuracion')
                ->cascadeOnDelete();
            $table->string('imagen');
            $table->string('tipo', 50)->nullable();
            $table->string('descripcion', 150)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colegio_imagenes');
    }
};
