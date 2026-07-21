<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colegio_configuracion', function (Blueprint $table) {
            $table->id('id_configuracion');
            $table->string('nombre_colegio');
            $table->string('logo')->nullable();
            $table->string('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('departamento')->nullable();
            $table->string('pais')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email_institucional')->nullable();
            $table->string('sitio_web')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // Fila única (id_configuracion = 1). El registro siempre existe desde
        // la instalación; ColegioConfiguracion::singleton() nunca necesita crearlo.
        DB::table('colegio_configuracion')->insert([
            'id_configuracion' => 1,
            'nombre_colegio' => 'Colegio San Cristóbal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('colegio_configuracion');
    }
};
