<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_galeria', function (Blueprint $table) {
            $table->id('id_foto');
            $table->string('imagen')->nullable();
            $table->string('descripcion', 150)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });

        $descripciones = ['Acto cívico', 'Deportes', 'Laboratorio', 'Arte', 'Graduación', 'Biblioteca'];

        $filas = collect($descripciones)->values()->map(fn ($descripcion, $i) => [
            'imagen' => null,
            'descripcion' => $descripcion,
            'orden' => $i + 1,
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        \Illuminate\Support\Facades\DB::table('landing_galeria')->insert($filas);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_galeria');
    }
};
