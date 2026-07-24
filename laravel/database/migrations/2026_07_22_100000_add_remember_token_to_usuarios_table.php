<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `usuarios` es la tabla legacy (nunca creada por una migración de Laravel).
 * Usuario extends Authenticatable, así que Auth::login($usuario, true) (checkbox
 * "Recordarme") intenta persistir remember_token y falla con
 * "Column not found: 1054 Unknown column 'remember_token'" porque la columna
 * nunca existió, tumbando el login con un 500.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usuarios', 'remember_token')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
