<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_admision', function (Blueprint $table) {
            $table->id('id_solicitud');
            $table->string('nombre_acudiente', 100);
            $table->string('apellido_acudiente', 100);
            $table->string('correo', 150);
            $table->string('telefono', 20);
            $table->string('nombre_estudiante', 150);
            $table->string('grado_interes', 50);
            $table->text('mensaje')->nullable();
            $table->enum('estado', ['pendiente', 'contactada', 'convertida', 'descartada'])->default('pendiente');
            $table->unsignedInteger('id_matricula')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_admision');
    }
};
