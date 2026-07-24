<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_categorias', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->string('nombre', 60);
            $table->string('slug', 60)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->string('color', 30);
            $table->string('icono', 60);
            $table->json('roles_crear');
            $table->json('roles_editar');
            $table->json('roles_eliminar');
            $table->json('roles_ver')->nullable();
            $table->boolean('es_sistema')->default(false);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_categorias');
    }
};
