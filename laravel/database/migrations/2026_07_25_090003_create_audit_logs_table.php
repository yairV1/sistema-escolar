<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora append-only de acciones críticas de la plataforma (módulo
 * Auditoria), mismo patrón que evento_historial: sin updated_at, nunca se
 * edita una fila ya escrita. `id_usuario`/`id_institucion` nullable porque
 * algunas acciones se registran antes de resolver sesión (intentos de
 * login fallidos) o son de alcance plataforma (sin institución asociada).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('id_log');
            // unsignedInteger, no foreignId: usuarios.id_usuario es INT UNSIGNED
            // (tabla legacy), no BIGINT UNSIGNED — mismo patrón que soportes.id_usuario.
            $table->unsignedInteger('id_usuario')->nullable();
            $table->foreignId('id_institucion')->nullable()->constrained('instituciones', 'id_institucion')->nullOnDelete();
            $table->string('accion');
            $table->string('entidad_tipo')->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('accion');
            $table->index(['entidad_tipo', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
