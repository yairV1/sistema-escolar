<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTA: originalmente este archivo (scaffold default de `laravel new`)
     * también creaba `users` y `password_reset_tokens`. Esta app autentica
     * contra la tabla `usuarios` del sistema legacy (ver App\Modules\Auth\Models\Usuario
     * y config/auth.php), así que esas dos tablas nunca se usaron y fueron
     * retiradas por la migración 2026_07_12_000001_drop_default_scaffold_tables.
     * Solo `sessions` es real (SESSION_DRIVER=database).
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
