<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('nombres', 150)->nullable();
            $table->string('apellidos', 150)->nullable();
            $table->string('tipo_documento', 20)->nullable();
            $table->string('numero_documento', 50)->nullable()->unique();
            $table->string('correo', 150)->nullable()->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('password')->nullable();
            $table->unsignedInteger('id_rol')->nullable();
            $table->unsignedBigInteger('id_institucion')->nullable();
            $table->string('estado_usuario', 30)->default('activo');
            $table->string('foto_perfil')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('sesion_valida_desde')->nullable();
            $table->string('ics_token', 64)->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
