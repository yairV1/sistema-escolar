<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Color de acento elegido por el propio usuario (hex, ej. "#0f9b8e"),
 * opcional — null significa "usar el color por defecto de mi rol". Ver
 * App\Shared\AccentColor, que traduce este valor a los tokens CSS que ya
 * usan los layouts (--sb-primary y derivados).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usuarios', 'color_acento')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('color_acento', 7)->nullable()->after('tema');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('color_acento');
        });
    }
};
