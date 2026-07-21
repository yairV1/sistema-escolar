<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de permisos de negocio (docs/arquitectura/03-rbac.md, seccion 3
 * y 10.1). Tabla nueva, no reemplaza ni modifica ninguna tabla existente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id('id_permiso');
            $table->string('slug', 100)->unique();
            $table->string('modulo', 60);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
