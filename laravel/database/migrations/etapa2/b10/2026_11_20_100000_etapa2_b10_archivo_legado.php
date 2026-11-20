<?php

use App\Core\Seguridad\GuardiaBaseDePruebas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * B10 · Archivo aislado de registros legados sin propietario.
 * Ejecutar explícitamente en una copia de pruebas, nunca como migración general.
 */
return new class extends Migration
{
    public function up(): void
    {
        GuardiaBaseDePruebas::asegurar(DB::connection(), basename(__FILE__, '.php'));
        if (! Schema::hasTable('migracion_pertenencia')) {
            throw new RuntimeException('B10 requiere M4 (migracion_pertenencia).');
        }

        Schema::table('migracion_pertenencia', fn (Blueprint $t) => $t->string('accion', 32)->change());

        if (Schema::hasTable('migracion_legado_archivos')) {
            $this->asegurarManifiesto();

            return;
        }

        Schema::create('migracion_legado_archivos', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->uuid('lote');
            $t->string('tabla_origen', 64);
            $t->unsignedBigInteger('id_origen');
            $t->longText('payload');
            $t->char('checksum_sha256', 64);
            $t->timestamp('archivado_at')->useCurrent();
            $t->timestamp('restaurado_at')->nullable();
            // Manifiesto de archivos públicos referenciados (ruta, existe, bytes, sha256), leído
            // sin modificarlos; va fuera del payload para no alterar su checksum.
            $t->longText('archivos')->nullable();
            $t->unique(['lote', 'tabla_origen', 'id_origen'], 'legado_lote_tabla_id_uq');
            $t->index(['tabla_origen', 'id_origen'], 'legado_origen_idx');
        });
    }

    private function asegurarManifiesto(): void
    {
        if (! Schema::hasColumn('migracion_legado_archivos', 'archivos')) {
            Schema::table('migracion_legado_archivos', fn (Blueprint $t) => $t->longText('archivos')->nullable()->after('restaurado_at'));
        }
    }

    public function down(): void
    {
        GuardiaBaseDePruebas::asegurar(DB::connection(), basename(__FILE__, '.php').' rollback');
        if (Schema::hasTable('migracion_legado_archivos') && DB::table('migracion_legado_archivos')->exists()) {
            throw new RuntimeException('No se revierte B10: el archivo legado conserva payloads de referencia. Restaurar los registros no elimina el historial; recuperar un respaldo si se requiere retirar esta tabla.');
        }
        if (DB::table('migracion_pertenencia')->where('accion', 'archivar_sin_propietario')->exists()) {
            throw new RuntimeException('No se revierte B10: migracion_pertenencia conserva trazas de archivo legado.');
        }

        Schema::dropIfExists('migracion_legado_archivos');
        Schema::table('migracion_pertenencia', fn (Blueprint $t) => $t->string('accion', 20)->change());
    }
};
