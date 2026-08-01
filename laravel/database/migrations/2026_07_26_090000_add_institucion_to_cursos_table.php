<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `cursos` no tiene ningún camino hacia una institución (no cuelga de
 * `usuarios`, a diferencia de estudiantes/profesores/matrículas). Sin esta
 * columna, "promedio por grado" del dashboard no puede aislarse por
 * tenant. Aditiva: se agrega nullable y se backfillea a la institución #1
 * (todo el dato existente pertenece hoy a ese único colegio).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('cursos', 'id_institucion')) {
            return;
        }

        Schema::table('cursos', function (Blueprint $table) {
            $table->foreignId('id_institucion')->nullable()->after('id_curso')
                ->constrained('instituciones', 'id_institucion')->nullOnDelete();
        });

        DB::table('cursos')->whereNull('id_institucion')->update(['id_institucion' => 1]);
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_institucion');
        });
    }
};
