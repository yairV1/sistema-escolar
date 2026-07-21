<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_noticias', function (Blueprint $table) {
            $table->id('id_noticia');
            $table->string('titulo', 200);
            $table->text('descripcion');
            $table->string('etiqueta', 50)->nullable();
            $table->string('imagen')->nullable();
            $table->date('fecha')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('landing_noticias')->insert([
            [
                'titulo' => 'Nuestros estudiantes ganan el campeonato regional de matemáticas',
                'descripcion' => 'Un equipo de 6 estudiantes de grado 11 obtuvo el primer lugar en la Olimpiada Regional de Matemáticas celebrada en la Universidad Nacional.',
                'etiqueta' => 'Logros',
                'fecha' => '2025-03-15',
                'orden' => 1,
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Festival de Arte y Cultura 2025',
                'descripcion' => 'Una semana llena de expresión artística, música y teatro protagonizada por nuestros estudiantes.',
                'etiqueta' => 'Cultura',
                'fecha' => '2025-03-08',
                'orden' => 2,
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Inauguración del nuevo laboratorio de ciencias',
                'descripcion' => 'Estrenamos un laboratorio completamente equipado con tecnología de punta para nuestros estudiantes de secundaria.',
                'etiqueta' => 'Ciencia',
                'fecha' => '2025-03-01',
                'orden' => 3,
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_noticias');
    }
};
