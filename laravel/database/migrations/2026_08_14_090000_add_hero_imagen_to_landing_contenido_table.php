<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Suma la clave 'hero_imagen' a landing_contenido (tabla clave-valor, ver
 * migración original 2026_07_18_150023). EditarLandingController::updateContenido()
 * usa UPDATE, no INSERT, así que sin esta fila sembrada la clave nunca se
 * podría guardar — mismo motivo por el que las demás claves (hero_badge,
 * hero_titulo, etc.) están sembradas ahí.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('landing_contenido')->where('clave', 'hero_imagen')->exists()) {
            return;
        }

        DB::table('landing_contenido')->insert([
            'clave' => 'hero_imagen',
            'valor' => null,
            'actualizado_en' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('landing_contenido')->where('clave', 'hero_imagen')->delete();
    }
};
