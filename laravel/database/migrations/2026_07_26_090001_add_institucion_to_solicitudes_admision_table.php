<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `solicitudes_admision` se crea desde el portal público (sin sesión), así
 * que no tiene ningún camino hacia una institución vía usuarios. Nullable +
 * backfill a la institución #1 (único colegio con portal público activo
 * hoy). Cuando el portal público sea realmente multi-tenant (Fase B, aún
 * no existe: dominios/slugs por institución), el formulario público deberá
 * resolver esta columna en el propio store(), no aquí.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('solicitudes_admision', 'id_institucion')) {
            return;
        }

        Schema::table('solicitudes_admision', function (Blueprint $table) {
            $table->foreignId('id_institucion')->nullable()->after('id_solicitud')
                ->constrained('instituciones', 'id_institucion')->nullOnDelete();
        });

        DB::table('solicitudes_admision')->whereNull('id_institucion')->update(['id_institucion' => 1]);
    }

    public function down(): void
    {
        Schema::table('solicitudes_admision', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_institucion');
        });
    }
};
