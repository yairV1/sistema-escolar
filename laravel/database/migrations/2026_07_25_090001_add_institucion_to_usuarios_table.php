<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `id_institucion` es nullable a propósito: el SuperAdmin (id_rol=8, ver
 * RolesSeeder) no pertenece a ninguna institución, opera la plataforma
 * completa. Todo usuario existente hoy pertenece a la única institución
 * real (#1, creada por la migración anterior), así que se backfillea en
 * el mismo up() — igual patrón que remember_token/ics_token: columna
 * nueva + dato consistente, sin paso manual posterior.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usuarios', 'id_institucion')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('id_institucion')
                ->nullable()
                ->after('id_usuario')
                ->constrained('instituciones', 'id_institucion')
                ->nullOnDelete();
        });

        DB::table('usuarios')->whereNull('id_institucion')->update(['id_institucion' => 1]);
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_institucion');
        });
    }
};
