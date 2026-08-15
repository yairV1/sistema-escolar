<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstruida desde el uso en código: tabla legacy sin migración propia
 * (App\Modules\Comunicados\Models\Notificacion). No confundir con
 * `notifications`, la tabla estándar de Laravel Notifications
 * (database/migrations/2026_07_24_100000_create_notifications_table.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->increments('id_notificacion');
            $table->string('titulo', 150);
            $table->string('mensaje', 2000);
            $table->unsignedInteger('id_usuario_origen');
            $table->unsignedInteger('id_usuario_destino');
            $table->enum('tipo_notificacion', ['informativa', 'academica', 'disciplinaria', 'pago', 'sistema']);
            $table->enum('canal', ['interno', 'correo', 'whatsapp', 'todos']);
            $table->timestamp('fecha_envio')->useCurrent();
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_lectura')->nullable();
            $table->string('estado', 20)->default('enviada');

            $table->foreign('id_usuario_origen')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
            $table->foreign('id_usuario_destino')->references('id_usuario')->on('usuarios')->cascadeOnDelete();

            $table->index('id_usuario_destino');
            $table->index('fecha_envio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
