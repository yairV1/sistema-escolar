<?php

namespace App\Modules\Instituciones\Poblacion;

use App\Core\Seguridad\GuardiaBaseDePruebas;
use App\Core\Seguridad\SoloLectura;
use App\Modules\Instituciones\Auditoria\AuditoriaPertenencia;
use Closure;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Población de id_institucion (Etapa 2, B4) — SOLO base de pruebas.
 *
 * 1. Guardia: conexión efectiva = colegio_tests con su usuario limitado
 *    (o SQLite en memoria dentro de la suite). Cualquier otra base: bloqueado.
 * 2. Esquema: M1–M4 aplicadas (16 columnas + migracion_pertenencia).
 * 3. Simulación nueva (solo lectura) y comparación de huella con el archivo.
 * 4. Validación completa del archivo (Correspondencia::resolver): si falta o
 *    es inválida una sola decisión, no se aplica nada.
 * 5. UNA transacción DML: asignar → clonar + repuntar → repuntar jerarquías →
 *    verificación (coherencia, huérfanos, conteos). Cualquier fallo = ROLLBACK.
 *    Nada de DDL dentro (en MariaDB provocaría un COMMIT implícito).
 * 6. Cada cambio queda en migracion_pertenencia (lote) para poder revertirlo.
 *
 * Nunca borra originales. Idempotente: un lote ya aplicado con la misma
 * huella no se vuelve a aplicar; cada UPDATE exige el valor previo esperado.
 */
class AplicadorPoblacion
{
    /** tabla que referencia => [pk usada en el registro, separador de rid compuesto] */
    private const PK_REFERENCIA = [
        'asignaciones_academicas' => 'id_asignacion', 'profesor_materia' => 'id_profesor', 'actividades' => 'id_actividad',
        'boletines' => 'id_boletin', 'eventos' => 'id_evento', 'materias' => 'id_materia', 'colegio_imagenes' => 'id_imagen',
    ];

    /** hijo.columna → tabla padre: el hijo debe apuntar a la versión de su padre de su misma institución */
    private const JERARQUIAS = [
        ['materias', 'id_materia', 'id_area', 'areas'],
        ['materias', 'id_materia', 'id_materia_padre', 'materias'],
        ['colegio_imagenes', 'id_imagen', 'id_configuracion', 'colegio_configuracion'],
    ];

    /** Tablas con FK hacia cada tabla clonable (para comprobar que una copia no tiene referencias antes de borrarla al revertir). */
    private const REFERENCIAS_FK = [
        'materias' => [['asignaciones_academicas', 'id_materia'], ['profesor_materia', 'id_materia'], ['materias', 'id_materia_padre']],
        'areas' => [['materias', 'id_area']],
        'periodos_academicos' => [['actividades', 'id_periodo'], ['boletines', 'id_periodo']],
        'tipos_actividad' => [['actividades', 'id_tipo_actividad']],
        'evento_categorias' => [['eventos', 'id_categoria']],
        'colegio_configuracion' => [['colegio_imagenes', 'id_configuracion']],
    ];

    private string $lote = '';

    private array $mapa = [];

    private int $pasos = 0;

    /** Solo pruebas: se invoca tras cada cambio con el número de paso (permite simular una interrupción). */
    public ?Closure $despuesDeCadaPaso = null;

    /** Solo pruebas: se invoca entre la validación previa y la operación protegida (simula un cambio concurrente). */
    public ?Closure $antesDeBloquear = null;

    /** Todo lo que la población lee o escribe (se bloquea durante la operación). */
    private const TABLAS_BLOQUEADAS = [
        'instituciones', 'usuarios', 'cursos', 'materias', 'areas', 'periodos_academicos', 'tipos_actividad', 'escala_notas', 'evento_categorias',
        'estudiantes', 'profesores', 'acudientes', 'eventos', 'soportes', 'colegio_configuracion', 'colegio_imagenes',
        'landing_contenido', 'landing_galeria', 'landing_noticias', 'asignaciones_academicas', 'profesor_materia', 'actividades',
        'boletines', 'matriculas', 'estudiante_acudiente', 'observaciones', 'detalle_notas', 'asistencia', 'boletin_detalle',
        'evento_participantes', 'notificaciones', 'migracion_pertenencia', 'migracion_legado_archivos',
    ];

    public function __construct(private Connection $db, private ?int $ejecutadoPor = null) {}

    // ----------------------------------------------------------- protecciones
    public static function asegurarBaseAutorizada(Connection $db): void
    {
        if ($motivo = GuardiaBaseDePruebas::motivoDeRechazo($db)) {
            throw new RuntimeException("BLOQUEADO: institucion:poblar --aplicar/--revertir solo están habilitados en la base de pruebas colegio_tests (B4). {$motivo}");
        }
        if ($db->getDriverName() === 'sqlite' && ! app()->runningUnitTests()) {
            throw new RuntimeException('BLOQUEADO: SQLite en memoria solo se acepta dentro de la suite de pruebas.');
        }
    }

    public function asegurarEsquema(): void
    {
        $schema = Schema::connection($this->db->getName());
        $faltan = array_values(array_filter(array_keys(SimuladorPoblacion::TABLAS), fn ($t) => ! $schema->hasColumn($t, 'id_institucion')));
        if ($faltan || ! $schema->hasTable('migracion_pertenencia')) {
            throw new RuntimeException('M1–M4 no están aplicadas por completo'.($faltan ? ' (falta id_institucion en '.implode(', ', $faltan).')' : ' (falta migracion_pertenencia)').'.');
        }
    }

    public function loteAplicado(string $huella): ?string
    {
        return $this->db->table('migracion_pertenencia')->where('accion', 'lote')->where('regla', 'huella:'.$huella)->whereNull('revertido_at')->value('lote');
    }

    // ---------------------------------------------------------------- aplicar
    /** @return array{estado:string, lote:?string, operaciones:array, errores:list<string>} */
    public function aplicar(array $archivo): array
    {
        self::asegurarBaseAutorizada($this->db);
        $this->asegurarEsquema();

        // 1) Validación previa (sin bloqueos): respuesta rápida ante archivos inválidos.
        $plan = SoloLectura::ejecutar($this->db, fn () => (new SimuladorPoblacion($this->db))->ejecutar());
        if ($lote = $this->loteAplicado((string) ($archivo['huella_plan'] ?? ''))) {
            return ['estado' => 'ya_aplicado', 'lote' => $lote, 'operaciones' => [], 'errores' => []];
        }
        [, $errores] = Correspondencia::resolver($archivo, $plan, $this->db);
        if ($errores) {
            return ['estado' => 'rechazado', 'lote' => null, 'operaciones' => [], 'errores' => $errores];
        }
        if ($this->antesDeBloquear) {
            ($this->antesDeBloquear)();   // solo pruebas: simula un cambio concurrente en esta ventana
        }

        // 2) Operación protegida: cerrojo exclusivo + transacción + filas bloqueadas.
        //    La huella y el archivo se vuelven a validar AQUÍ, sobre datos que ya nadie puede cambiar.
        try {
            return $this->conCerrojo(function () use ($archivo) {
                $this->db->transaction(function () use ($archivo) {
                    $this->bloquearFilas();
                    $plan = SoloLectura::ejecutar($this->db, fn () => (new SimuladorPoblacion($this->db))->ejecutar());
                    if ($lote = $this->loteAplicado($plan['huella']) ?? $this->loteAplicado((string) ($archivo['huella_plan'] ?? ''))) {
                        throw new RechazoPoblacion([], $lote);
                    }
                    [$ops, $errores] = Correspondencia::resolver($archivo, $plan, $this->db);
                    if ($errores) {
                        throw new RechazoPoblacion($errores);
                    }
                    $this->ejecutarOperaciones($ops, $plan);
                });

                return ['estado' => 'aplicado', 'lote' => $this->lote, 'operaciones' => $this->resumenLote($this->lote), 'errores' => []];
            });
        } catch (RechazoPoblacion $r) {
            return $r->lote
                ? ['estado' => 'ya_aplicado', 'lote' => $r->lote, 'operaciones' => [], 'errores' => []]
                : ['estado' => 'rechazado', 'lote' => null, 'operaciones' => [], 'errores' => $r->errores];
        }
    }

    private function ejecutarOperaciones(array $ops, array $plan): void
    {
        $this->lote = (string) Str::uuid();
        $this->mapa = [];
        $this->pasos = 0;
        $clones = collect($plan['clonaciones'])->keyBy(fn ($c) => $c['tabla'].'#'.$c['id']);
        $orden = array_flip(array_keys(SimuladorPoblacion::TABLAS));
        uasort($ops, function ($a, $b) use ($orden) {
            $archivoA = $a['accion'] === 'archivar_sin_propietario';
            $archivoB = $b['accion'] === 'archivar_sin_propietario';
            $prioridadArchivo = ['colegio_imagenes' => 0, 'colegio_configuracion' => 1];
            $claveA = [$archivoA ? 0 : 1, $archivoA ? ($prioridadArchivo[$a['tabla']] ?? 2) : $orden[$a['tabla']], $a['id']];
            $claveB = [$archivoB ? 0 : 1, $archivoB ? ($prioridadArchivo[$b['tabla']] ?? 2) : $orden[$b['tabla']], $b['id']];

            return $claveA <=> $claveB;
        });

        $antes = $this->conteos();
        $this->registrar('lote', '_lote', 0, null, null, null, null, null, 'correspondencia', 'huella:'.$plan['huella']);
        foreach ($ops as $op) {
            if ($op['accion'] === 'seguir_configuracion') {
                $op = $this->resolverSeguimiento($op, $plan);
            }
            match ($op['accion']) {
                'asignar' => $this->asignar($op['tabla'], $op['id'], $op['destino'], $op),
                'clonar' => $this->clonar($op, $clones[$op['tabla'].'#'.$op['id']] ?? null),
                'archivar_sin_propietario' => $this->archivar($op),
                'plantilla', 'descartar' => $this->sinCambio($op),
            };
        }
        $this->repuntarJerarquias();
        $this->verificar($ops, $antes);
    }

    /**
     * Cerrojo exclusivo de la operación (MariaDB: GET_LOCK, liberado siempre al
     * terminar). Impide dos aplicaciones/reversiones simultáneas sobre la misma
     * base. En SQLite (solo suite de pruebas) el motor ya serializa escrituras.
     */
    private function conCerrojo(Closure $operacion): mixed
    {
        if (! in_array($this->db->getDriverName(), ['mysql', 'mariadb'], true)) {
            return $operacion();
        }
        $nombre = 'institucion_poblar:'.$this->db->getDatabaseName();
        if ((int) $this->db->selectOne('SELECT GET_LOCK(?, 0) AS c', [$nombre])->c !== 1) {
            throw new RuntimeException('Otra aplicación o reversión de pertenencia está en curso sobre esta base. No se modificó nada.');
        }
        try {
            return $operacion();
        } finally {
            $this->db->selectOne('SELECT RELEASE_LOCK(?) AS r', [$nombre]);
        }
    }

    /**
     * Dentro de la transacción y ANTES de cualquier lectura no bloqueante:
     * bloquea (FOR UPDATE, con bloqueo de huecos en REPEATABLE READ) todas las
     * filas que la población lee o escribe. Nadie puede insertar, modificar ni
     * borrar en esas tablas hasta el COMMIT/ROLLBACK; la instantánea de la
     * simulación posterior refleja exactamente los datos bloqueados.
     */
    private function bloquearFilas(): void
    {
        if (! in_array($this->db->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;   // SQLite: la transacción ya excluye otros escritores
        }
        foreach (self::TABLAS_BLOQUEADAS as $t) {
            if ($t === 'migracion_legado_archivos' && ! Schema::connection($this->db->getName())->hasTable($t)) {
                continue;
            }
            $this->db->select("SELECT COUNT(*) AS n FROM `{$t}` FOR UPDATE");
        }
    }

    private function archivar(array $op): void
    {
        $resultado = (new ArchivoLegado($this->db))->archivar($this->lote, $op['tabla'], $op['id']);
        $this->registrar(
            'archivar_sin_propietario',
            $op['tabla'],
            $op['id'],
            null,
            null,
            $resultado['checksum'],
            null,
            $resultado['id'],
            $op['origen'],
            $op['regla'],
        );
    }

    private function asignar(string $tabla, int $id, int $inst, array $op): void
    {
        $pk = SimuladorPoblacion::TABLAS[$tabla][0];
        $n = $this->db->table($tabla)->where($pk, $id)->whereNull('id_institucion')->update(['id_institucion' => $inst]);
        if ($n !== 1) {
            throw new RuntimeException("{$tabla}#{$id}: se esperaba id_institucion NULL (afectadas: {$n}). Estado inconsistente; no se aplica nada.");
        }
        $this->mapa[$tabla][$id][$inst] = $id;
        $this->registrar('asignar', $tabla, $id, 'id_institucion', null, (string) $inst, $inst, null, $op['origen'], $op['regla']);
    }

    private function sinCambio(array $op): void
    {
        $pk = SimuladorPoblacion::TABLAS[$op['tabla']][0];
        if ($this->db->table($op['tabla'])->where($pk, $op['id'])->whereNotNull('id_institucion')->exists()) {
            throw new RuntimeException("{$op['tabla']}#{$op['id']}: {$op['accion']} exige id_institucion NULL.");
        }
        $this->registrar($op['accion'], $op['tabla'], $op['id'], null, null, null, null, null, $op['origen'], $op['regla']);
    }

    private function clonar(array $op, ?array $clon): void
    {
        $tabla = $op['tabla'];
        $pk = SimuladorPoblacion::TABLAS[$tabla][0];
        if ($clon && $clon['referencias_sin_institucion']) {
            throw new RuntimeException("{$tabla}#{$op['id']}: tiene referencias sin institución determinable; no se puede clonar sin perderlas.");
        }
        $this->asignar($tabla, $op['id'], $op['conserva'], $op);

        $fila = (array) $this->db->table($tabla)->where($pk, $op['id'])->first();
        foreach ($op['clones'] as $inst) {
            if ($inst === $op['conserva']) {
                continue;
            }
            $copia = $fila;
            unset($copia[$pk]);
            $copia['id_institucion'] = $inst;
            $nuevo = (int) $this->db->table($tabla)->insertGetId($copia, $pk);
            $this->mapa[$tabla][$op['id']][$inst] = $nuevo;
            $this->registrar('clonar', $tabla, $op['id'], null, null, null, $inst, $nuevo, $op['origen'], $op['regla']);

            // Referencias directas que pertenecen a esta institución → a su copia.
            // (áreas, materia padre y configuración se repuntan después, por jerarquía.)
            foreach ($clon['referencias_por_institucion'][$inst]['filas'] ?? [] as $ref) {
                if (in_array($ref['tabla'].'.'.$ref['columna'], ['materias.id_area', 'colegio_imagenes.id_configuracion'], true)) {
                    continue;
                }
                $pkRef = self::PK_REFERENCIA[$ref['tabla']];
                $idRef = (int) explode(':', $ref['rid'])[0];
                $this->repuntar($ref['tabla'], $pkRef, $idRef, $ref['columna'], $op['id'], $nuevo, $inst, $op);
            }
        }
    }

    private function repuntar(string $tabla, string $pk, int $id, string $col, int $de, int $a, ?int $inst, array $op): void
    {
        $n = $this->db->table($tabla)->where($pk, $id)->where($col, $de)->update([$col => $a]);
        if ($n !== 1) {
            throw new RuntimeException("{$tabla}#{$id}.{$col}: se esperaba {$de} (afectadas: {$n}).");
        }
        $this->registrar('repuntar', $tabla, $id, $col, (string) $de, (string) $a, $inst, null, $op['origen'], $op['regla']);
    }

    /** Las imágenes que siguen a su configuración toman su misma decisión. */
    private function resolverSeguimiento(array $op, array $plan): array
    {
        $dep = collect(collect($plan['registros'])->first(fn ($r) => $r['tabla'] === 'colegio_imagenes' && $r['id'] === $op['id'])['dependencias'] ?? [])->firstWhere('tabla', 'colegio_configuracion');
        $versiones = $this->mapa['colegio_configuracion'][$dep['padre'] ?? 0] ?? [];
        if (! $versiones) {
            throw new RuntimeException("colegio_imagenes#{$op['id']}: su configuración no tiene institución asignada.");
        }
        $insts = array_keys($versiones);
        sort($insts);
        $conserva = array_search($dep['padre'], $versiones, true);

        return count($insts) === 1
            ? ['accion' => 'asignar', 'destino' => $insts[0]] + $op
            : ['accion' => 'clonar', 'clones' => $insts, 'conserva' => $conserva] + $op;
    }

    /** Cada hijo debe apuntar a la versión de su padre que pertenece a su misma institución. */
    private function repuntarJerarquias(): void
    {
        foreach (self::JERARQUIAS as [$tabla, $pk, $col, $padre]) {
            if (empty($this->mapa[$padre])) {
                continue;
            }
            foreach ($this->db->table($tabla)->whereNotNull($col)->whereNotNull('id_institucion')->orderBy($pk)->get([$pk, $col, 'id_institucion']) as $h) {
                $versiones = $this->mapa[$padre][(int) $h->{$col}] ?? null;
                if ($versiones === null) {
                    continue;   // el padre no se tocó en este lote; el verificador comprueba la coherencia
                }
                $destino = $versiones[(int) $h->id_institucion] ?? null;
                if ($destino === null) {
                    throw new RuntimeException("{$tabla}#{$h->{$pk}} (institución {$h->id_institucion}) apunta a {$padre}#{$h->{$col}}, que no tiene versión para su institución. Decidir clonar o asignar el padre en coherencia.");
                }
                if ($destino !== (int) $h->{$col}) {
                    $this->repuntar($tabla, $pk, (int) $h->{$pk}, $col, (int) $h->{$col}, $destino, (int) $h->id_institucion, ['origen' => 'regla', 'regla' => 'jerarquia']);
                }
            }
        }
    }

    private function verificar(array $ops, array $antes): void
    {
        $violaciones = (new VerificadorPertenencia($this->db))->violaciones();
        if ($violaciones) {
            throw new RuntimeException('Verificación fallida: '.count($violaciones).' violación(es) de coherencia, p. ej. '.json_encode(array_slice($violaciones, 0, 3)).'. Se revierte todo.');
        }
        $audit = (new AuditoriaPertenencia($this->db))->ejecutar()['hallazgos'];
        $graves = array_filter($audit, fn ($h) => in_array($h['seccion'], ['referencias_huerfanas', 'relaciones_cruzadas'], true));
        if ($graves) {
            throw new RuntimeException('Verificación fallida: '.count($graves).' referencia(s) huérfana(s) o cruzada(s). Se revierte todo.');
        }
        foreach ($ops as $op) {
            $pk = SimuladorPoblacion::TABLAS[$op['tabla']][0];
            $valor = $this->db->table($op['tabla'])->where($pk, $op['id'])->value('id_institucion');
            $debeTener = in_array($op['accion'], ['asignar', 'clonar', 'seguir_configuracion'], true);
            if ($debeTener === ($valor === null)) {
                throw new RuntimeException("Verificación fallida: {$op['tabla']}#{$op['id']} quedó con id_institucion ".var_export($valor, true)." tras {$op['accion']}.");
            }
        }
        $esperado = $antes;
        foreach ($this->db->table('migracion_pertenencia')->where('lote', $this->lote)->where('accion', 'clonar')->get() as $c) {
            $esperado[$c->tabla]++;
        }
        foreach ($this->db->table('migracion_pertenencia')->where('lote', $this->lote)->where('accion', 'archivar_sin_propietario')->get() as $a) {
            $esperado[$a->tabla]--;
            if ($this->db->table($a->tabla)->where(SimuladorPoblacion::TABLAS[$a->tabla][0], $a->id_registro)->exists()
                || ! $this->db->table('migracion_legado_archivos')->where('id', $a->id_registro_nuevo)
                    ->where('tabla_origen', $a->tabla)->where('id_origen', $a->id_registro)
                    ->where('checksum_sha256', $a->valor_nuevo)->exists()) {
                throw new RuntimeException("Verificación fallida: el archivo de {$a->tabla}#{$a->id_registro} no coincide con su traza/checksum.");
            }
        }
        if ($this->conteos() !== $esperado) {
            throw new RuntimeException('Verificación fallida: los conteos de filas no coinciden con originales + copias. Se revierte todo.');
        }
    }

    private function conteos(): array
    {
        return collect(array_keys(SimuladorPoblacion::TABLAS))->mapWithKeys(fn ($t) => [$t => (int) $this->db->table($t)->count()])->all();
    }

    private function registrar(string $accion, string $tabla, int $id, ?string $col, ?string $ant, ?string $nuevo, ?int $inst, ?int $idNuevo, string $origen, ?string $regla): void
    {
        $this->db->table('migracion_pertenencia')->insert([
            'lote' => $this->lote, 'accion' => $accion, 'tabla' => $tabla, 'id_registro' => $id, 'columna' => $col,
            'valor_anterior' => $ant, 'valor_nuevo' => $nuevo, 'id_institucion' => $inst, 'id_registro_nuevo' => $idNuevo,
            'origen' => $origen, 'regla' => $regla === null ? null : mb_substr($regla, 0, 80), 'ejecutado_por' => $this->ejecutadoPor, 'created_at' => now(),
        ]);
        $this->pasos++;
        if ($this->despuesDeCadaPaso) {
            ($this->despuesDeCadaPaso)($this->pasos, $accion, $tabla);
        }
    }

    public function resumenLote(string $lote): array
    {
        return $this->db->table('migracion_pertenencia')->where('lote', $lote)->selectRaw('accion, COUNT(*) AS n')->groupBy('accion')->orderBy('accion')->pluck('n', 'accion')->map(fn ($n) => (int) $n)->all();
    }

    // --------------------------------------------------------------- revertir
    /** Deshace un lote en orden inverso, en una transacción. Nunca borra originales; solo copias sin referencias. */
    public function revertir(string $lote): array
    {
        self::asegurarBaseAutorizada($this->db);
        $this->asegurarEsquema();
        $filas = $this->db->table('migracion_pertenencia')->where('lote', $lote)->whereNull('revertido_at')->orderByDesc('id')->get();
        if ($filas->isEmpty()) {
            return ['estado' => 'nada_que_revertir', 'lote' => $lote];
        }
        $this->asegurarColumnasArchivadasRestaurables($lote);

        return $this->conCerrojo(fn () => $this->revertirProtegido($lote));
    }

    private function revertirProtegido(string $lote): array
    {
        $filas = null;
        $this->db->transaction(function () use (&$filas, $lote) {
            $this->bloquearFilas();
            $filas = $this->db->table('migracion_pertenencia')->where('lote', $lote)->whereNull('revertido_at')->orderByDesc('id')->get();
            if ($filas->isEmpty()) {
                return;
            }
            $this->asegurarColumnasArchivadasRestaurables($lote);
            foreach ($filas as $f) {
                $pkPropia = SimuladorPoblacion::TABLAS[$f->tabla][0] ?? null;
                match ($f->accion) {
                    'repuntar' => $this->esperarUna($this->db->table($f->tabla)->where(self::PK_REFERENCIA[$f->tabla], $f->id_registro)->where($f->columna, $f->valor_nuevo)->update([$f->columna => $f->valor_anterior]), $f),
                    'clonar' => $this->borrarCopia($f, $pkPropia),
                    'asignar' => $this->esperarUna($this->db->table($f->tabla)->where($pkPropia, $f->id_registro)->where('id_institucion', $f->valor_nuevo)->update(['id_institucion' => null]), $f),
                    'archivar_sin_propietario' => (new ArchivoLegado($this->db))->restaurar(
                        (int) $f->id_registro_nuevo,
                        $f->tabla,
                        (int) $f->id_registro,
                        (string) $f->valor_nuevo,
                    ),
                    default => null,
                };
            }
            $this->db->table('migracion_pertenencia')->where('lote', $lote)->whereNull('revertido_at')->update(['revertido_at' => now()]);
        });

        return $filas->isEmpty() ? ['estado' => 'nada_que_revertir', 'lote' => $lote] : ['estado' => 'revertido', 'lote' => $lote, 'filas' => $filas->count()];
    }

    private function asegurarColumnasArchivadasRestaurables(string $lote): void
    {
        $trazas = $this->db->table('migracion_pertenencia')->where('lote', $lote)->where('accion', 'archivar_sin_propietario')->get(['tabla', 'id_registro']);
        foreach ($trazas as $traza) {
            $columna = collect(Schema::connection($this->db->getName())->getColumns($traza->tabla))->firstWhere('name', 'id_institucion');
            if ($columna && ! (bool) $columna['nullable']) {
                throw new RuntimeException("No se revierte: primero debe revertirse B8 para restaurar {$traza->tabla}#{$traza->id_registro} sin propietario.");
            }
        }
    }

    private function borrarCopia(object $f, string $pk): void
    {
        foreach (self::REFERENCIAS_FK[$f->tabla] ?? [] as [$t, $c]) {
            if ($this->db->table($t)->where($c, $f->id_registro_nuevo)->exists()) {
                throw new RuntimeException("No se revierte: la copia {$f->tabla}#{$f->id_registro_nuevo} tiene referencias en {$t}.{$c} creadas después de la población.");
            }
        }
        $this->esperarUna($this->db->table($f->tabla)->where($pk, $f->id_registro_nuevo)->where('id_institucion', $f->id_institucion)->delete(), $f);
    }

    private function esperarUna(int $n, object $f): void
    {
        if ($n !== 1) {
            throw new RuntimeException("No se revierte: {$f->accion} {$f->tabla}#{$f->id_registro} ya no está en el estado registrado (afectadas: {$n}). Nada se modificó.");
        }
    }
}
