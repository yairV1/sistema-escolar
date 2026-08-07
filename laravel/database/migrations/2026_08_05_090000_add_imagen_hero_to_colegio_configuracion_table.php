<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto grande que se muestra en la sección "hero" (inicio) de la landing
 * pública, junto al título y las estadísticas. Antes de esto solo había un
 * ícono de placeholder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colegio_configuracion', function (Blueprint $table) {
            $table->string('imagen_hero')->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('colegio_configuracion', function (Blueprint $table) {
            $table->dropColumn('imagen_hero');
        });
    }
};
