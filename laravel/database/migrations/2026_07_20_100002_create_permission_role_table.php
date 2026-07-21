<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivote roles <-> permissions (docs/arquitectura/03-rbac.md, seccion 10.1
 * y 10.3). Representa la matriz Rol -> Permisos aprobada en la seccion 4
 * de ese documento. Nombre de tabla `permission_role` por convencion
 * estandar de Eloquent para pivotes implicitos (orden alfabetico de los
 * modelos). No se crea `user_role`: la seccion 10.3 del diseño ya
 * justifica por que no aplica en esta fase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedTinyInteger('id_rol');
            $table->unsignedBigInteger('id_permiso');

            $table->primary(['id_rol', 'id_permiso']);

            $table->foreign('id_rol')
                ->references('id_rol')->on('roles')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('id_permiso')
                ->references('id_permiso')->on('permissions')
                ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
