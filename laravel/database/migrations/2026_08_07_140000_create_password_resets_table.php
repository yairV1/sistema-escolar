<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id('id_reset');
            $table->unsignedInteger('id_usuario');
            $table->string('token_hash', 64);
            $table->timestamp('expira_en');
            $table->boolean('usado')->default(false);

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();

            $table->index('token_hash');
            $table->index(['id_usuario', 'usado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
