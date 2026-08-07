<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite adjuntar una captura de pantalla al reportar un problema, para
 * que el equipo de soporte vea directamente lo que el usuario está viendo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            $table->string('imagen_ruta', 500)->nullable()->after('mensaje');
            $table->string('imagen_nombre_original', 255)->nullable()->after('imagen_ruta');
        });
    }

    public function down(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            $table->dropColumn(['imagen_ruta', 'imagen_nombre_original']);
        });
    }
};
