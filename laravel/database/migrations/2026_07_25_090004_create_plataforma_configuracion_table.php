<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Parámetros globales de la plataforma (límites de plan por defecto,
 * plantilla de comunicado global, contacto de soporte). Registro único
 * (id_configuracion = 1), mismo patrón que colegio_configuracion.
 *
 * Deliberadamente NO incluye credenciales de integraciones (WhatsApp Cloud
 * API ya vive en .env / config('services.whatsapp_cloud'), ver
 * config/services.php): los secretos de integración se gestionan por
 * entorno, no en una tabla editable desde el panel — el panel de
 * SuperAdmin solo muestra su estado, no los reemplaza.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plataforma_configuracion', function (Blueprint $table) {
            $table->id('id_configuracion');
            $table->unsignedInteger('limite_usuarios_default')->nullable();
            $table->unsignedInteger('limite_instituciones')->nullable();
            $table->text('plantilla_comunicado_default')->nullable();
            $table->string('soporte_email_contacto')->nullable();
            $table->timestamps();
        });

        DB::table('plataforma_configuracion')->insert([
            'id_configuracion' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plataforma_configuracion');
    }
};
