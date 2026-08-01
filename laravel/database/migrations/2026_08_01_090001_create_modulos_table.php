<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de módulos funcionales que un plan puede incluir (Matrículas,
 * Boletines, Portal de padres...). Es metadata de plataforma —qué se
 * ofrece por plan—, no un mecanismo de feature-flagging real todavía: los
 * ~13 módulos de negocio existentes no leen esta tabla para activarse
 * (eso pertenece a la Fase B de aislamiento multi-tenant, ver
 * docs/arquitectura/10-superadmin-plataforma.md §2). Por ahora es
 * exclusivamente informativo/comercial en el panel SuperAdmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modulos', function (Blueprint $table) {
            $table->id('id_modulo');
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('categoria');
            $table->text('descripcion')->nullable();
            $table->string('icono')->default('fas fa-puzzle-piece');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};
