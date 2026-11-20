<?php

use App\Core\Seguridad\GuardiaBaseDePruebas;
use App\Modules\Instituciones\Auditoria\AuditoriaPertenencia;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * B8 · R1–R3. Se ejecuta explícitamente con --path=database/migrations/etapa2/b8.
 * Solo DDL: sin población, asignación ni normalización de datos.
 */
return new class extends Migration
{
    private const OBLIGATORIAS = [
        'cursos', 'solicitudes_admision', 'materias', 'areas', 'periodos_academicos',
        'tipos_actividad', 'estudiantes', 'profesores', 'acudientes', 'eventos', 'soportes',
        'colegio_configuracion', 'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias',
    ];

    private const UNICOS = [
        'cursos' => [
            'anterior' => ['nombre_curso', 'anio_lectivo', 'jornada'],
            'nuevo' => ['id_institucion', 'nombre_curso', 'anio_lectivo', 'jornada'],
            'nombre' => 'cursos_inst_nombre_anio_jornada_uq',
        ],
        'estudiantes' => [
            'anterior' => ['codigo_estudiante'],
            'nuevo' => ['id_institucion', 'codigo_estudiante'],
            'nombre' => 'estudiantes_inst_codigo_uq',
        ],
        'profesores' => [
            'anterior' => ['codigo_profesor'],
            'nuevo' => ['id_institucion', 'codigo_profesor'],
            'nombre' => 'profesores_inst_codigo_uq',
        ],
        'colegio_configuracion' => [
            'anterior' => [],
            'nuevo' => ['id_institucion'],
            'nombre' => 'colegio_configuracion_inst_uq',
        ],
    ];

    public function up(): void
    {
        GuardiaBaseDePruebas::asegurar(DB::connection(), basename(__FILE__, '.php'));
        $this->validarDatos();

        $fksSuspendidas = $this->suspenderFksHijasEnSqlite();
        try {
            foreach (self::UNICOS as $tabla => $unico) {
                $this->asegurarUnicoCompuesto($tabla, $unico);
            }

            $this->asegurarFkRestrict('cursos');
            $this->asegurarFkRestrict('solicitudes_admision');
            if (DB::connection()->getDriverName() !== 'sqlite') {
                // Nullable for SuperAdmin; MariaDB can add the FK without rebuilding referenced usuarios.
                $this->asegurarFkRestrict('usuarios');
            }

            foreach (self::OBLIGATORIAS as $tabla) {
                $columna = collect(Schema::getColumns($tabla))->firstWhere('name', 'id_institucion');
                if ($columna['nullable']) {
                    Schema::table($tabla, fn (Blueprint $t) => $t->unsignedBigInteger('id_institucion')->nullable(false)->change());
                }
                $this->asegurarFkRestrict($tabla);
            }
            $this->asegurarFkRestrict('escala_notas');
            $this->asegurarFkRestrict('evento_categorias');
        } finally {
            $this->restaurarFksHijasEnSqlite($fksSuspendidas);
        }
    }

    public function down(): void
    {
        GuardiaBaseDePruebas::asegurar(DB::connection(), basename(__FILE__, '.php').' rollback');
        $this->validarReversion();

        $fksSuspendidas = $this->suspenderFksHijasEnSqlite();
        try {
            foreach (self::UNICOS as $tabla => $unico) {
                $this->restaurarUnicoGlobal($tabla, $unico);
            }

            foreach (self::OBLIGATORIAS as $tabla) {
                $columna = collect(Schema::getColumns($tabla))->firstWhere('name', 'id_institucion');
                if (! $columna['nullable']) {
                    Schema::table($tabla, fn (Blueprint $t) => $t->unsignedBigInteger('id_institucion')->nullable()->change());
                }
            }
            $this->asegurarFkSetNull('cursos');
            $this->asegurarFkSetNull('solicitudes_admision');
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $this->asegurarFkSetNull('usuarios');
            }
        } finally {
            $this->restaurarFksHijasEnSqlite($fksSuspendidas);
        }
    }

    private function validarDatos(): void
    {
        if (! Schema::hasTable('instituciones')) {
            throw new RuntimeException('B8 requiere la tabla instituciones.');
        }
        if (! Schema::hasColumn('usuarios', 'id_institucion')) {
            throw new RuntimeException('B8 requiere B2 completa: falta usuarios.id_institucion.');
        }
        foreach (['evento_categorias', 'escala_notas'] as $tabla) {
            if (! Schema::hasColumn($tabla, 'id_institucion')) {
                throw new RuntimeException("B8 requiere B2 completa: falta {$tabla}.id_institucion.");
            }
        }

        $pendientes = [];
        foreach (self::OBLIGATORIAS as $tabla) {
            if (! Schema::hasColumn($tabla, 'id_institucion')) {
                throw new RuntimeException("B8 requiere B2 completa: falta {$tabla}.id_institucion.");
            }
            $sinInstitucion = DB::table($tabla)->whereNull('id_institucion')->count();
            if ($sinInstitucion > 0) {
                $pendientes[] = "{$tabla} ({$sinInstitucion})";
            }
            $huerfanas = DB::table($tabla.' as t')
                ->leftJoin('instituciones as i', 'i.id_institucion', '=', 't.id_institucion')
                ->whereNotNull('t.id_institucion')
                ->whereNull('i.id_institucion')
                ->count();
            if ($huerfanas > 0) {
                throw new RuntimeException("B8 bloqueada: {$tabla} contiene referencias institucionales huérfanas.");
            }
        }
        if ($pendientes !== []) {
            throw new RuntimeException('B8 bloqueada: filas sin institución o con decisión pendiente en '.implode(', ', $pendientes).'. No se ejecutó DDL.');
        }

        foreach (['cursos' => ['nombre_curso', 'anio_lectivo', 'jornada'], 'estudiantes' => ['codigo_estudiante'], 'profesores' => ['codigo_profesor'], 'colegio_configuracion' => ['id_institucion']] as $tabla => $columnas) {
            if ($this->hayDuplicados($tabla, array_values(array_unique(array_merge(['id_institucion'], $columnas))))) {
                throw new RuntimeException("B8 bloqueada: {$tabla} ya contiene duplicados dentro de una institución.");
            }
        }

        if ($this->hayDuplicados('colegio_configuracion', ['id_institucion'])) {
            throw new RuntimeException('B8 bloqueada: hay más de una configuración para una institución.');
        }

        $reporte = (new AuditoriaPertenencia(DB::connection()))->ejecutar();
        $bloqueos = array_filter($reporte['hallazgos'], fn ($h) => $h['severidad'] === AuditoriaPertenencia::CRITICO
            && in_array($h['seccion'], ['relaciones_cruzadas', 'referencias_huerfanas', 'coherencia_institucional'], true));
        if ($bloqueos !== []) {
            $primero = reset($bloqueos);
            throw new RuntimeException("B8 bloqueada: integridad padre-hijo pendiente en {$primero['tabla']} (hallazgo {$primero['seccion']}). No se ejecutó DDL.");
        }
    }

    private function validarReversion(): void
    {
        foreach (self::UNICOS as $tabla => $unico) {
            if ($unico['anterior'] !== [] && $this->hayDuplicados($tabla, $unico['anterior'])) {
                throw new RuntimeException("No se revierte B8: {$tabla} contiene valores que colisionan con el único global anterior.");
            }
        }
    }

    private function asegurarUnicoCompuesto(string $tabla, array $unico): void
    {
        $compuesto = $this->indicesUnicos($tabla, $unico['nuevo']);
        if (count($compuesto) > 1) {
            throw new RuntimeException("B8 encontró índices compuestos duplicados en {$tabla}; revisar manualmente antes de continuar.");
        }
        if ($compuesto === []) {
            Schema::table($tabla, fn (Blueprint $t) => $t->unique($unico['nuevo'], $unico['nombre']));
        }

        if ($unico['anterior'] === []) {
            return;
        }

        $globales = $this->indicesUnicos($tabla, $unico['anterior']);
        if (count($globales) > 1) {
            throw new RuntimeException("B8 encontró más de un índice global esperado en {$tabla}; revisar dependencias manualmente.");
        }
        if ($globales !== []) {
            Schema::table($tabla, fn (Blueprint $t) => $t->dropUnique($globales[0]['name']));
        }
    }

    private function restaurarUnicoGlobal(string $tabla, array $unico): void
    {
        if ($unico['anterior'] !== []) {
            $globales = $this->indicesUnicos($tabla, $unico['anterior']);
            if ($globales === []) {
                $nombre = $tabla.'_'.implode('_', $unico['anterior']).'_unique';
                Schema::table($tabla, fn (Blueprint $t) => $t->unique($unico['anterior'], $nombre));
            }
        }

        if ($unico['nuevo'][0] === 'id_institucion' && ! collect(Schema::getIndexes($tabla))
            ->contains(fn ($i) => $i['columns'] === ['id_institucion'])) {
            Schema::table($tabla, fn (Blueprint $t) => $t->index('id_institucion', "{$tabla}_id_institucion_index"));
        }

        $compuestos = $this->indicesUnicos($tabla, $unico['nuevo']);
        foreach ($compuestos as $indice) {
            Schema::table($tabla, fn (Blueprint $t) => $t->dropUnique($indice['name']));
        }
    }

    private function indicesUnicos(string $tabla, array $columnas): array
    {
        if ($columnas === []) {
            return [];
        }

        return collect(Schema::getIndexes($tabla))
            ->filter(fn ($i) => $i['unique'] && ! $i['primary'] && $i['columns'] === $columnas)
            ->values()
            ->all();
    }

    private function hayDuplicados(string $tabla, array $columnas): bool
    {
        return DB::table($tabla)->select($columnas)->groupBy($columnas)->havingRaw('COUNT(*) > 1')->limit(1)->exists();
    }

    private function asegurarFkRestrict(string $tabla): void
    {
        $fk = collect(Schema::getForeignKeys($tabla))
            ->first(fn ($f) => $f['columns'] === ['id_institucion'] && $f['foreign_table'] === 'instituciones');

        if ($fk !== null && in_array(strtolower($fk['on_delete']), ['restrict', 'no action'], true)) {
            return;
        }

        $conservarFkLegacyEnSqlite = $tabla === 'usuarios'
            && DB::connection()->getDriverName() === 'sqlite'
            && $fk !== null
            && strtolower($fk['on_delete']) === 'set null';

        if ($fk !== null) {
            if (! $conservarFkLegacyEnSqlite) {
                if (! in_array($tabla, ['cursos', 'solicitudes_admision', 'usuarios'], true) || strtolower($fk['on_delete']) !== 'set null') {
                    throw new RuntimeException("B8 no reemplaza automáticamente la FK existente de {$tabla}.id_institucion.");
                }
                Schema::table($tabla, fn (Blueprint $t) => $t->dropForeign(['id_institucion']));
            }
        }

        if (! collect(Schema::getIndexes($tabla))->contains(fn ($i) => $i['columns'] === ['id_institucion'])) {
            Schema::table($tabla, fn (Blueprint $t) => $t->index('id_institucion', "{$tabla}_id_institucion_index"));
        }

        if ($conservarFkLegacyEnSqlite) {
            return;
        }

        Schema::table($tabla, fn (Blueprint $t) => $t->foreign('id_institucion', "{$tabla}_id_institucion_foreign")
            ->references('id_institucion')->on('instituciones')->restrictOnDelete());
    }

    private function asegurarFkSetNull(string $tabla): void
    {
        $fk = collect(Schema::getForeignKeys($tabla))
            ->first(fn ($f) => $f['columns'] === ['id_institucion'] && $f['foreign_table'] === 'instituciones');

        if ($fk !== null && strtolower($fk['on_delete']) === 'set null') {
            return;
        }
        if ($fk !== null) {
            Schema::table($tabla, fn (Blueprint $t) => $t->dropForeign(['id_institucion']));
        }

        Schema::table($tabla, fn (Blueprint $t) => $t->foreign('id_institucion', "{$tabla}_id_institucion_foreign")
            ->references('id_institucion')->on('instituciones')->nullOnDelete());
    }

    /**
     * SQLite no permite desactivar PRAGMA foreign_keys dentro de una transacción.
     * Re-construir una tabla padre en ese contexto ejecuta sus ON DELETE y puede
     * borrar hijos; se suspenden las FK afectadas de hojas a padres y se restauran
     * en orden inverso.
     */
    private function suspenderFksHijasEnSqlite(): array
    {
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->transactionLevel() === 0) {
            return [];
        }

        $schema = Schema::connection(DB::getDefaultConnection());
        $fksPorTabla = [];
        foreach ($schema->getTables() as $tabla) {
            $nombre = $tabla['name'];
            $fksPorTabla[$nombre] = $schema->getForeignKeys($nombre);
        }

        $afectadas = array_fill_keys(array_merge(self::OBLIGATORIAS, ['cursos', 'solicitudes_admision']), true);
        $seleccionadas = [];
        do {
            $cantidadAnterior = count($afectadas);
            foreach ($fksPorTabla as $tabla => $fks) {
                foreach ($fks as $fk) {
                    if ($tabla === $fk['foreign_table'] || ! isset($afectadas[$fk['foreign_table']])) {
                        continue;
                    }
                    $clave = $tabla.'|'.implode(',', $fk['columns']).'|'.$fk['foreign_table'];
                    $seleccionadas[$clave] = ['tabla' => $tabla] + $fk;
                    $afectadas[$tabla] = true;
                }
            }
        } while (count($afectadas) !== $cantidadAnterior);

        $pendientes = $afectadas;
        $orden = [];
        while ($pendientes !== []) {
            $hojas = [];
            foreach (array_keys($pendientes) as $tabla) {
                $tieneHijaPendiente = collect($seleccionadas)->contains(
                    fn ($fk) => $fk['foreign_table'] === $tabla && $fk['tabla'] !== $tabla && isset($pendientes[$fk['tabla']])
                );
                if (! $tieneHijaPendiente) {
                    $hojas[] = $tabla;
                }
            }
            if ($hojas === []) {
                throw new RuntimeException('B8 SQLite bloqueada: el grafo de claves foráneas afectadas contiene un ciclo no suspendible; no se modificó DDL.');
            }
            sort($hojas);
            foreach ($hojas as $tabla) {
                $orden[] = $tabla;
                unset($pendientes[$tabla]);
            }
        }

        $suspendidas = [];
        try {
            foreach ($orden as $tabla) {
                $fksTabla = array_values(array_filter($seleccionadas, fn ($fk) => $fk['tabla'] === $tabla));
                if ($fksTabla === []) {
                    continue;
                }
                Schema::table($tabla, function (Blueprint $t) use ($fksTabla) {
                    foreach ($fksTabla as $fk) {
                        $t->dropForeign($fk['columns']);
                    }
                });
                $suspendidas = array_merge($suspendidas, $fksTabla);
            }
        } catch (\Throwable $e) {
            $this->restaurarFksHijasEnSqlite(['foreign_keys' => $suspendidas, 'orden' => $orden]);
            throw $e;
        }

        return ['foreign_keys' => $suspendidas, 'orden' => $orden];
    }

    private function restaurarFksHijasEnSqlite(array $suspendidas): void
    {
        if ($suspendidas === []) {
            return;
        }

        $fks = $suspendidas['foreign_keys'] ?? [];
        foreach (array_reverse($suspendidas['orden'] ?? []) as $tabla) {
            $fksTabla = array_values(array_filter($fks, fn ($fk) => $fk['tabla'] === $tabla));
            if ($fksTabla === []) {
                continue;
            }
            Schema::table($tabla, function (Blueprint $t) use ($fksTabla) {
                foreach ($fksTabla as $fk) {
                    $constraint = $t->foreign($fk['columns'])
                        ->references($fk['foreign_columns'])->on($fk['foreign_table']);
                    $constraint->onDelete($fk['on_delete'])->onUpdate($fk['on_update']);
                }
            });
        }
    }
};
