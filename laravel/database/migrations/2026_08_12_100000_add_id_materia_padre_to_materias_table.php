<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->unsignedInteger('id_materia_padre')->nullable()->after('id_materia');
            $table->foreign('id_materia_padre')->references('id_materia')->on('materias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropForeign(['id_materia_padre']);
            $table->dropColumn('id_materia_padre');
        });
    }
};
