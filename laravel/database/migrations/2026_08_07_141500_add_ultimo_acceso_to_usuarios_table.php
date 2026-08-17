<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca de tiempo del último login exitoso, seteada en
 * App\Modules\Auth\Controllers\LoginController y TwoFactorChallengeController.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usuarios', 'ultimo_acceso')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->timestamp('ultimo_acceso')->nullable()->after('sesion_valida_desde');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('ultimo_acceso');
        });
    }
};
