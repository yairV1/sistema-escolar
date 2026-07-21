<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración baseline no destructiva (Plan Maestro de Implementación,
 * seccion 9, paso 7 / docs/arquitectura/03-rbac.md, seccion 10.1):
 * `roles` es una tabla heredada del sistema legacy que nunca tuvo
 * migración propia. En los entornos actuales la tabla ya existe con
 * datos reales, por lo que up() no la toca. En un entorno nuevo
 * (instalación desde cero) la crea con el esquema real, para que el
 * proyecto sea reproducible sin depender de un dump externo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            return;
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->tinyIncrements('id_rol');
            $table->string('nombre_rol', 50);
            $table->string('descripcion', 200)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->unique('nombre_rol', 'uq_nombre_rol');
        });
    }

    public function down(): void
    {
        // Baseline no destructivo: `roles` existía antes de esta migración
        // en todo entorno real y contiene datos de producción (usuarios,
        // matrículas, etc. dependen de ella por FK). Revertir esta
        // migración nunca debe eliminar la tabla heredada.
    }
};
