<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reconstruida desde el uso en código: tabla legacy sin migración propia (App\Modules\Usuarios\Models\Acudiente). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acudientes', function (Blueprint $table) {
            $table->increments('id_acudiente');
            $table->unsignedInteger('id_usuario')->unique();
            $table->string('ocupacion', 100)->nullable();
            $table->string('empresa', 100)->nullable();
            $table->string('estado', 20)->default('activo');

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acudientes');
    }
};
