<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Fase A de la plataforma multi-tenant (docs/arquitectura/10-superadmin-plataforma.md):
 * `instituciones` es el registro de tenants que el SuperAdmin administra.
 * Institución #1 se crea aquí mismo leyendo los datos reales de
 * `colegio_configuracion` (única fila existente hoy), igual que esa propia
 * migración se auto-insertó su fila única — así el colegio que ya opera en
 * este sistema queda de alta como la primera institución sin intervención
 * manual, y `usuarios.id_institucion` (migración siguiente) puede apuntar
 * a ella desde el primer momento.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id('id_institucion');
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('nit')->nullable();
            $table->string('email_contacto')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('pais')->nullable();
            $table->string('logo')->nullable();
            $table->string('plan')->default('basico');
            $table->unsignedInteger('limite_usuarios')->nullable();
            $table->enum('estado', ['activa', 'suspendida', 'inactiva'])->default('activa');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
        });

        $colegio = Schema::hasTable('colegio_configuracion')
            ? DB::table('colegio_configuracion')->where('id_configuracion', 1)->first()
            : null;

        DB::table('instituciones')->insert([
            'id_institucion' => 1,
            'nombre' => $colegio->nombre_colegio ?? 'Colegio San Cristóbal',
            'slug' => Str::slug($colegio->nombre_colegio ?? 'colegio-san-cristobal'),
            'email_contacto' => $colegio->email_institucional ?? null,
            'telefono' => $colegio->telefono ?? null,
            'direccion' => $colegio->direccion ?? null,
            'ciudad' => $colegio->ciudad ?? null,
            'pais' => $colegio->pais ?? null,
            'logo' => $colegio->logo ?? null,
            'plan' => 'premium',
            'estado' => 'activa',
            'fecha_inicio' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('instituciones');
    }
};
