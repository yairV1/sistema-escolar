<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Etapa 2 · M4 — trazabilidad de la transformación de pertenencia institucional
 * (ver docs/seguridad/etapa2-b2.md). Esta migración solo crea la tabla; nadie
 * escribe en ella hasta institucion:poblar (B3/B4, no autorizado aún).
 *
 * Una fila por cambio atómico:
 *  - asignar:   tabla.id_registro recibe id_institucion (valor_anterior = NULL)
 *  - clonar:    tabla.id_registro se duplica como id_registro_nuevo para id_institucion
 *  - repuntar:  tabla.columna de id_registro pasa de valor_anterior a valor_nuevo
 *  - plantilla: el registro queda como plantilla global (id_institucion NULL)
 *  - descartar: el registro se excluye de la migración (no se borra)
 * Con estas filas, en orden inverso por lote, se puede deshacer una población.
 *
 * Sin datos personales ni secretos: solo identificadores, nombres de tabla y
 * columna, la regla o decisión aplicada y quién/cuándo. Deliberadamente SIN
 * claves foráneas: el historial debe sobrevivir a cambios en los registros
 * que describe.
 */
return new class extends Migration
{
    public function up(): void
    {
        \App\Core\Seguridad\GuardiaMigracionesEtapa2::asegurar(DB::connection(), basename(__FILE__, ".php"));

        if (Schema::hasTable('migracion_pertenencia')) {
            return;
        }

        Schema::create('migracion_pertenencia', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->uuid('lote')->comment('Agrupa todos los cambios de una ejecución');
            $t->string('accion', 20)->comment('asignar | clonar | repuntar | plantilla | descartar');
            $t->string('tabla', 64);
            $t->unsignedBigInteger('id_registro');
            $t->string('columna', 64)->nullable()->comment('Columna modificada (repuntar) o id_institucion');
            $t->string('valor_anterior', 64)->nullable();
            $t->string('valor_nuevo', 64)->nullable();
            $t->unsignedBigInteger('id_institucion')->nullable()->comment('Institución resultante');
            $t->unsignedBigInteger('id_registro_nuevo')->nullable()->comment('Id del clon creado (clonar)');
            $t->string('origen', 20)->comment('regla | correspondencia');
            $t->string('regla', 80)->nullable()->comment('Regla de pertenencia o entrada del archivo de correspondencia');
            $t->unsignedInteger('ejecutado_por')->nullable()->comment('id_usuario que ejecutó (sin FK: historial)');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('revertido_at')->nullable();

            $t->index('lote');
            $t->index(['tabla', 'id_registro']);
            $t->index('id_institucion');
        });
    }

    public function down(): void
    {
        // Solo si está vacía: si ya registró una población, borrarla destruiría
        // la información necesaria para revertirla. En ese caso se aborta.
        if (! Schema::hasTable('migracion_pertenencia')) {
            return;
        }
        if (DB::table('migracion_pertenencia')->exists()) {
            throw new RuntimeException('migracion_pertenencia contiene registros de una población: no se elimina. Revertir primero la población (o restaurar el respaldo).');
        }
        Schema::drop('migracion_pertenencia');
    }
};
