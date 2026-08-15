<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Escala de desempeño (Bajo/Básico/Alto/Superior) que el colegio usa para
 * traducir la nota definitiva numérica a una sigla en el boletín. Se siembra
 * con los rangos estándar del MEN, editables desde Calificaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escala_notas', function (Blueprint $table) {
            $table->increments('id_escala');
            $table->string('etiqueta', 50);
            $table->string('sigla', 5);
            $table->decimal('valor_min', 3, 1);
            $table->decimal('valor_max', 3, 1);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        $ahora = now();
        DB::table('escala_notas')->insert([
            ['etiqueta' => 'Bajo', 'sigla' => 'Bj', 'valor_min' => 0.0, 'valor_max' => 2.9, 'orden' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['etiqueta' => 'Básico', 'sigla' => 'Bs', 'valor_min' => 3.0, 'valor_max' => 3.9, 'orden' => 2, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['etiqueta' => 'Alto', 'sigla' => 'A', 'valor_min' => 4.0, 'valor_max' => 4.5, 'orden' => 3, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['etiqueta' => 'Superior', 'sigla' => 'S', 'valor_min' => 4.6, 'valor_max' => 5.0, 'orden' => 4, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('escala_notas');
    }
};
