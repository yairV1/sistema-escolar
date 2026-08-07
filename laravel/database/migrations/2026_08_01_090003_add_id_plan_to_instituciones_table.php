<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enlaza cada institución al catálogo estructurado de `planes`, sin tocar
 * la columna `plan` (string) que ya existía: migración no destructiva,
 * mismo criterio que el resto de `docs/arquitectura/10-superadmin-plataforma.md`.
 * `id_plan` es nullable a propósito — una institución puede quedar sin
 * plan catalogado (dato legado) y seguir mostrando su `plan` de texto.
 * El backfill que enlaza instituciones existentes por su valor de texto
 * ('basico'/'estandar'/'premium') vive en PlanesSeeder, no aquí, porque
 * depende de que el catálogo ya esté sembrado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->foreignId('id_plan')->nullable()->after('plan')
                ->constrained('planes', 'id_plan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_plan');
        });
    }
};
