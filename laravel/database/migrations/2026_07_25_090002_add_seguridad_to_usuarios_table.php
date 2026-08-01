<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soporte de 2FA (TOTP) y de invalidación forzada de sesión al cambiar rol
 * o permisos — ver Core\Seguridad\TwoFactorService y
 * Core\Http\Middleware\EnsureSessionFresh. Columnas nullable: sin efecto
 * para las cuentas que no activan 2FA ni sufren cambios de rol.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usuarios', 'two_factor_secret')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('ics_token');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->timestamp('sesion_valida_desde')->nullable()->after('two_factor_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'sesion_valida_desde',
            ]);
        });
    }
};
