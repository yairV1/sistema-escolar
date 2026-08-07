<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de planes comerciales de la plataforma (evolución del campo
 * `instituciones.plan`, que hasta ahora era un string libre validado por
 * enum en InstitucionStoreRequest). No sustituye esa columna — se
 * mantiene por compatibilidad de lectura/filtros existentes — sino que
 * agrega una fuente de verdad estructurada (precio, límites, módulos
 * incluidos) que `instituciones.id_plan` referenciará (ver migración
 * add_id_plan_to_instituciones_table).
 *
 * Deliberadamente NO es facturación: no hay pasarela de pago en el
 * proyecto (docs/arquitectura/10-superadmin-plataforma.md §2, columna
 * "No incluido"). `precio_mensual`/`precio_anual` son metadata comercial
 * mostrada en el panel, igual que `fecha_vencimiento` en `instituciones`
 * ya lo era antes de esta migración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id('id_plan');
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('precio_mensual', 12, 2)->default(0);
            $table->decimal('precio_anual', 12, 2)->default(0);
            $table->unsignedInteger('limite_usuarios')->nullable();
            $table->json('beneficios')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
