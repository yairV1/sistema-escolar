<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Etapa 2 · M3 — institución en configuración y landing (EXPANSIÓN, ver docs/seguridad/etapa2-b2.md).
 *
 * Solo agrega `id_institucion` NULLABLE + índice + FK a instituciones con
 * ON DELETE RESTRICT. NO puebla datos (a diferencia de la migración antigua de
 * cursos, que asignaba la institución 1 por defecto), no cambia únicos ni
 * nulabilidad de nada existente.
 *
 * RESTRICT es seguro en esta etapa: todas las filas quedan en NULL, así que no
 * bloquea ninguna operación actual; cuando se pueblen, impedirá borrar una
 * institución que aún tiene datos (en vez de dejarlos huérfanos con SET NULL).
 *
 * El DDL de MariaDB no es transaccional: cada pieza (columna, índice, FK) se
 * verifica por separado, de modo que una ejecución interrumpida puede reanudarse.
 */
return new class extends Migration
{
    /** tabla => columna tras la cual se ubica id_institucion */
    private const TABLAS = [
        'colegio_configuracion' => 'id_configuracion',
        'colegio_imagenes' => 'id_imagen',
        'landing_contenido' => 'id_contenido',
        'landing_galeria' => 'id_foto',
        'landing_noticias' => 'id_noticia',
    ];

    public function up(): void
    {
        \App\Core\Seguridad\GuardiaMigracionesEtapa2::asegurar(DB::connection(), basename(__FILE__, ".php"));

        // Cada pieza (columna, índice, FK) se verifica por separado: si una
        // ejecución se interrumpe entre dos ALTER, la siguiente completa lo que falte.
        foreach (self::TABLAS as $tabla => $despuesDe) {
            if (! Schema::hasColumn($tabla, 'id_institucion')) {
                Schema::table($tabla, fn (Blueprint $t) => $t->unsignedBigInteger('id_institucion')->nullable()->after($despuesDe));
            }
            if (! Schema::hasIndex($tabla, "{$tabla}_id_institucion_index")) {
                Schema::table($tabla, fn (Blueprint $t) => $t->index('id_institucion', "{$tabla}_id_institucion_index"));
            }
            if (! $this->tieneFk($tabla)) {
                Schema::table($tabla, fn (Blueprint $t) => $t->foreign('id_institucion', "{$tabla}_id_institucion_foreign")
                    ->references('id_institucion')->on('instituciones')->restrictOnDelete());
            }
        }
    }

    /**
     * Elimina solo lo que up() agregó. Se niega si alguna fila ya tiene
     * institución asignada (población posterior): borrar la columna perdería
     * esa pertenencia. En ese caso se revierte primero la población.
     */
    public function down(): void
    {
        foreach (self::TABLAS as $tabla => $despuesDe) {
            if (Schema::hasColumn($tabla, 'id_institucion') && DB::table($tabla)->whereNotNull('id_institucion')->exists()) {
                throw new RuntimeException("{$tabla}.id_institucion ya tiene valores asignados: no se elimina. Revertir primero la población (migracion_pertenencia) o restaurar el respaldo.");
            }
        }
        foreach (array_reverse(self::TABLAS, true) as $tabla => $despuesDe) {
            if ($this->tieneFk($tabla)) {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropForeign(['id_institucion']));   // por columna: en MariaDB resuelve al mismo nombre «<tabla>_id_institucion_foreign»; SQLite solo admite esta forma
            }
            if (Schema::hasIndex($tabla, "{$tabla}_id_institucion_index")) {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropIndex("{$tabla}_id_institucion_index"));
            }
            if (Schema::hasColumn($tabla, 'id_institucion')) {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropColumn('id_institucion'));
            }
        }
    }

    private function tieneFk(string $tabla): bool
    {
        foreach (Schema::getForeignKeys($tabla) as $fk) {
            if ($fk['columns'] === ['id_institucion'] && $fk['foreign_table'] === 'instituciones') {
                return true;
            }
        }

        return false;
    }
};
