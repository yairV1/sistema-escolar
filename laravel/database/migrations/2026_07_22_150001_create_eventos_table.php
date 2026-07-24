<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id('id_evento');
            $table->unsignedBigInteger('id_categoria');
            $table->unsignedInteger('id_usuario_creador');
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->time('hora_inicio')->nullable();
            $table->date('fecha_fin');
            $table->time('hora_fin')->nullable();
            $table->boolean('todo_el_dia')->default(false);
            $table->string('color_override', 30)->nullable();
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('estado', ['pendiente', 'completado', 'cancelado'])->default('pendiente');
            $table->enum('visibilidad', ['privado', 'publico', 'compartido'])->default('privado');
            $table->unsignedInteger('id_curso')->nullable();
            $table->unsignedInteger('id_asignacion')->nullable();
            $table->string('salon', 60)->nullable();
            $table->string('ubicacion', 150)->nullable();
            $table->enum('tipo_recurrencia', ['ninguna', 'diaria', 'semanal', 'mensual'])->default('ninguna');
            $table->unsignedSmallInteger('intervalo_recurrencia')->nullable();
            $table->json('dias_semana_recurrencia')->nullable();
            $table->date('fecha_fin_recurrencia')->nullable();
            $table->json('recordatorio_minutos_antes')->nullable();
            $table->enum('estado_activo', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->foreign('id_categoria')->references('id_categoria')->on('evento_categorias')->restrictOnDelete();
            $table->foreign('id_usuario_creador')->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->nullOnDelete();
            $table->foreign('id_asignacion')->references('id_asignacion')->on('asignaciones_academicas')->nullOnDelete();

            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('visibilidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
