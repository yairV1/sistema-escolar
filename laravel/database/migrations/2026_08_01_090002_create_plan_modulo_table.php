<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivote plan <-> módulo ("qué módulos incluye cada plan"), mismo patrón
 * de pivote sin PK propia que `permission_role`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_modulo', function (Blueprint $table) {
            $table->foreignId('id_plan')->constrained('planes', 'id_plan')->cascadeOnDelete();
            $table->foreignId('id_modulo')->constrained('modulos', 'id_modulo')->cascadeOnDelete();
            $table->primary(['id_plan', 'id_modulo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_modulo');
    }
};
