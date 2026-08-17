<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Preferencias personales de cuenta: idioma de la interfaz (base para ir
 * traduciendo la app pantalla por pantalla, ver lang/en.json), si el
 * usuario quiere recibir comunicados por correo, y el tema claro/oscuro
 * elegido — hoy ese último solo vivía en localStorage del navegador, así
 * que no lo seguía entre dispositivos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usuarios', 'idioma')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('idioma', 5)->default('es')->after('correo');
            $table->boolean('notificaciones_email')->default(true)->after('idioma');
            $table->string('tema', 10)->nullable()->after('notificaciones_email');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['idioma', 'notificaciones_email', 'tema']);
        });
    }
};
