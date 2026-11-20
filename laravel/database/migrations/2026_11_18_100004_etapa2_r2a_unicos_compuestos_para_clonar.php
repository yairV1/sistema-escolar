<?php

use App\Core\Seguridad\GuardiaBaseDePruebas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Etapa 2 · R2a — R2 ANTICIPADO, solo lo que la clonación de B4 necesita
 * (docs/seguridad/etapa2-b4.md).
 *
 * Reemplaza, en las 6 tablas de catálogo/landing que pueden clonarse, el único
 * GLOBAL por uno COMPUESTO con id_institucion: primero crea el compuesto y
 * después retira el global, de modo que nunca queda la tabla sin unicidad.
 * Los únicos de identidad (usuarios, codigo_estudiante, codigo_profesor) NO se
 * tocan: los perfiles no se clonan.
 *
 * Limitación conocida: en un único compuesto, las filas con id_institucion NULL
 * (plantillas, o datos aún sin poblar) no chocan entre sí. Mientras haya filas
 * en NULL, la unicidad entre ellas depende de la aplicación y de
 * institucion:auditar; por eso EventoCategoriaSeeder verifica el slug antes de
 * insertar.
 *
 * Autorización (más estricta que M1–M4): base de pruebas (GuardiaBaseDePruebas)
 * o ETAPA2_R2_AUTORIZADA=<nombre exacto de la base> en el entorno del proceso.
 * Sobre la base real corresponde a B8 y requiere autorización propia.
 */
return new class extends Migration
{
    /** tabla => columnas del único actual */
    private const UNICOS = [
        'materias' => ['nombre_materia'],
        'areas' => ['nombre_area'],
        'tipos_actividad' => ['nombre_tipo'],
        'periodos_academicos' => ['nombre_periodo', 'anio_lectivo'],
        'evento_categorias' => ['slug'],
        'landing_contenido' => ['clave'],
    ];

    public function up(): void
    {
        $this->autorizar();

        foreach (self::UNICOS as $tabla => $cols) {
            [$global, $compuesto] = $this->nombres($tabla, $cols);
            if (! Schema::hasColumn($tabla, 'id_institucion')) {
                throw new RuntimeException("R2a requiere M1–M3 aplicadas: falta {$tabla}.id_institucion.");
            }
            if (! Schema::hasIndex($tabla, $compuesto)) {
                Schema::table($tabla, fn (Blueprint $t) => $t->unique(array_merge(['id_institucion'], $cols), $compuesto));
            }
            if (Schema::hasIndex($tabla, $global)) {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropUnique($global));
            }
        }
    }

    /**
     * Restaura los únicos globales. Se niega (antes de cualquier DDL) si hay
     * valores repetidos —p. ej. tras clonar—: restaurar fallaría a medias.
     * En ese caso primero se revierte la población (institucion:poblar --revertir).
     */
    public function down(): void
    {
        foreach (self::UNICOS as $tabla => $cols) {
            $dup = DB::table($tabla)->select($cols)->groupBy($cols)->havingRaw('COUNT(*) > 1')->limit(1)->get();
            if ($dup->isNotEmpty()) {
                throw new RuntimeException("{$tabla} tiene valores repetidos en (".implode(', ', $cols).'): no se puede restaurar el único global. Revertir primero la población (institucion:poblar --revertir).');
            }
        }
        foreach (array_reverse(self::UNICOS, true) as $tabla => $cols) {
            [$global, $compuesto] = $this->nombres($tabla, $cols);
            if (! Schema::hasIndex($tabla, $global)) {
                Schema::table($tabla, fn (Blueprint $t) => $t->unique($cols, $global));
            }
            if (Schema::hasIndex($tabla, $compuesto)) {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropUnique($compuesto));
            }
        }
    }

    private function nombres(string $tabla, array $cols): array
    {
        // MariaDB limita los identificadores a 64 caracteres: el compuesto usa la forma corta «_inst_…_uq»
        return ["{$tabla}_".implode('_', $cols).'_unique', "{$tabla}_inst_".implode('_', $cols).'_uq'];
    }

    private function autorizar(): void
    {
        $c = DB::connection();
        if ($c->pretending() || GuardiaBaseDePruebas::esAutorizada($c)) {
            return;
        }
        try {
            $base = $c->getDriverName() === 'sqlite' ? null : (string) $c->selectOne('SELECT DATABASE() AS b')->b;
        } catch (Throwable) {
            $base = null;
        }
        $permitida = getenv('ETAPA2_R2_AUTORIZADA');
        if ($base === null || $permitida === false || $permitida !== $base) {
            throw new RuntimeException('BLOQUEADO: R2a (únicos compuestos) solo se aplica en la base de pruebas o con ETAPA2_R2_AUTORIZADA=<nombre exacto de la base>. No se ejecutó ningún cambio.');
        }
    }
};
