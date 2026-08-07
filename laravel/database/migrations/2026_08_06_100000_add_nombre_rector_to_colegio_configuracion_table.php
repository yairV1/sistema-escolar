<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Nombre impreso en la línea de firma del rector en el PDF del boletín. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colegio_configuracion', function (Blueprint $table) {
            $table->string('nombre_rector')->nullable()->after('nombre_colegio');
        });
    }

    public function down(): void
    {
        Schema::table('colegio_configuracion', function (Blueprint $table) {
            $table->dropColumn('nombre_rector');
        });
    }
};
