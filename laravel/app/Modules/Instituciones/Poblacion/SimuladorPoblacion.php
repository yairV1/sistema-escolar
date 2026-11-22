<?php

namespace App\Modules\Instituciones\Poblacion;

use App\Modules\Instituciones\Auditoria\AuditoriaPertenencia;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Schema;

/**
 * Simulación de la población de id_institucion (Etapa 2, bloque B3).
 *
 * SOLO LECTURA: únicamente SELECT (el comando la ejecuta además dentro de
 * SoloLectura, que lo impone en el motor). No asigna, no clona, no repunta
 * referencias, no modifica identificadores. Produce un PLAN determinista.
 *
 * Funciona con o sin las columnas de B2: si la tabla tiene id_institucion se
 * compara su valor actual con lo inferido; si no, se infiere igual.
 *
 * Reglas (docs/seguridad/etapa2-diseno.md §5): la institución se deduce de
 * los USOS de cada registro, nunca se asume una por defecto. Ambigüedad →
 * decisión manual en el archivo de correspondencia.
 */
class SimuladorPoblacion
{
    // Situaciones (clasificación por registro)
    public const INEQUIVOCA = 'inequivoca';

    public const YA_ASIGNADO = 'ya_asignado';

    public const COMPARTIDO = 'compartido';

    public const SIN_PROPIETARIO = 'sin_uso_sin_propietario';

    public const PLANTILLA = 'plantilla';

    public const HUERFANO = 'huerfano';

    public const INCONSISTENTE = 'relacion_inconsistente';

    public const MANUAL = 'decision_manual';

    /** tabla => [pk, columna descriptiva, plantilla permitida por el diseño] */
    public const TABLAS = [
        'materias' => ['id_materia', 'nombre_materia', false],
        'areas' => ['id_area', 'nombre_area', false],
        'periodos_academicos' => ['id_periodo', "CONCAT(nombre_periodo, ' ', anio_lectivo)", false],
        'tipos_actividad' => ['id_tipo_actividad', 'nombre_tipo', false],
        'escala_notas' => ['id_escala', 'etiqueta', true],
        'evento_categorias' => ['id_categoria', 'nombre', true],
        'estudiantes' => ['id_estudiante', 'codigo_estudiante', false],
        'profesores' => ['id_profesor', 'codigo_profesor', false],
        'acudientes' => ['id_acudiente', 'id_usuario', false],
        'eventos' => ['id_evento', 'titulo', false],
        'soportes' => ['id_soporte', 'asunto', false],
        'colegio_configuracion' => ['id_configuracion', 'nombre_colegio', false],
        'colegio_imagenes' => ['id_imagen', 'tipo', false],
        'landing_contenido' => ['id_contenido', 'clave', false],
        'landing_galeria' => ['id_foto', 'imagen', false],
        'landing_noticias' => ['id_noticia', 'titulo', false],
    ];

    /** Configuración y landing: SOLO por decisión humana (diseño §5). */
    private const SOLO_CORRESPONDENCIA = ['colegio_configuracion', 'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias'];

    /**
     * Usos que determinan la institución. Cada consulta devuelve
     * rid (fila que referencia), id (registro de la tabla), inst (institución de esa fila o NULL).
     * tabla => [[tabla_ref, columna_ref, sql], ...]
     */
    private const USOS = [
        'materias' => [
            ['asignaciones_academicas', 'id_materia', 'SELECT a.id_asignacion AS rid, a.id_materia AS id, c.id_institucion AS inst FROM asignaciones_academicas a LEFT JOIN cursos c ON c.id_curso = a.id_curso'],
            ['profesor_materia', 'id_materia', "SELECT CONCAT(pm.id_profesor, ':', pm.id_materia) AS rid, pm.id_materia AS id, u.id_institucion AS inst FROM profesor_materia pm LEFT JOIN profesores p ON p.id_profesor = pm.id_profesor LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario"],
        ],
        'periodos_academicos' => [
            ['actividades', 'id_periodo', 'SELECT ac.id_actividad AS rid, ac.id_periodo AS id, c.id_institucion AS inst FROM actividades ac LEFT JOIN asignaciones_academicas a ON a.id_asignacion = ac.id_asignacion LEFT JOIN cursos c ON c.id_curso = a.id_curso'],
            ['boletines', 'id_periodo', 'SELECT b.id_boletin AS rid, b.id_periodo AS id, u.id_institucion AS inst FROM boletines b LEFT JOIN estudiantes e ON e.id_estudiante = b.id_estudiante LEFT JOIN usuarios u ON u.id_usuario = e.id_usuario'],
        ],
        'tipos_actividad' => [
            ['actividades', 'id_tipo_actividad', 'SELECT ac.id_actividad AS rid, ac.id_tipo_actividad AS id, c.id_institucion AS inst FROM actividades ac LEFT JOIN asignaciones_academicas a ON a.id_asignacion = ac.id_asignacion LEFT JOIN cursos c ON c.id_curso = a.id_curso'],
        ],
        'evento_categorias' => [
            ['eventos', 'id_categoria', 'SELECT ev.id_evento AS rid, ev.id_categoria AS id, u.id_institucion AS inst FROM eventos ev LEFT JOIN usuarios u ON u.id_usuario = ev.id_usuario_creador'],
        ],
        'colegio_configuracion' => [
            ['colegio_imagenes', 'id_configuracion', 'SELECT i.id_imagen AS rid, i.id_configuracion AS id, NULL AS inst FROM colegio_imagenes i'],
        ],
    ];

    /** Perfiles y recursos: institución del usuario dueño. tabla => sql (id, id_usuario, existe_usuario, inst, rol) */
    private const DUENO = [
        'estudiantes' => 'SELECT t.id_estudiante AS id, t.id_usuario AS uid, u.id_usuario AS existe, u.id_institucion AS inst, u.id_rol AS rol FROM estudiantes t LEFT JOIN usuarios u ON u.id_usuario = t.id_usuario',
        'profesores' => 'SELECT t.id_profesor AS id, t.id_usuario AS uid, u.id_usuario AS existe, u.id_institucion AS inst, u.id_rol AS rol FROM profesores t LEFT JOIN usuarios u ON u.id_usuario = t.id_usuario',
        'acudientes' => 'SELECT t.id_acudiente AS id, t.id_usuario AS uid, u.id_usuario AS existe, u.id_institucion AS inst, u.id_rol AS rol FROM acudientes t LEFT JOIN usuarios u ON u.id_usuario = t.id_usuario',
        'eventos' => 'SELECT t.id_evento AS id, t.id_usuario_creador AS uid, u.id_usuario AS existe, u.id_institucion AS inst, u.id_rol AS rol FROM eventos t LEFT JOIN usuarios u ON u.id_usuario = t.id_usuario_creador',
        'soportes' => 'SELECT t.id_soporte AS id, t.id_usuario AS uid, u.id_usuario AS existe, u.id_institucion AS inst, u.id_rol AS rol FROM soportes t LEFT JOIN usuarios u ON u.id_usuario = t.id_usuario',
    ];

    /** Evidencia secundaria de institución para perfiles/eventos (detecta incoherencias). tabla => [[descripcion, sql (id, inst)]] */
    private const EVIDENCIA = [
        'estudiantes' => [['matrícula en curso', 'SELECT m.id_estudiante AS id, c.id_institucion AS inst FROM matriculas m JOIN cursos c ON c.id_curso = m.id_curso']],
        'profesores' => [
            ['asignación en curso', 'SELECT a.id_profesor AS id, c.id_institucion AS inst FROM asignaciones_academicas a JOIN cursos c ON c.id_curso = a.id_curso'],
            ['dirección de grupo', 'SELECT c.id_director_grupo AS id, c.id_institucion AS inst FROM cursos c WHERE c.id_director_grupo IS NOT NULL'],
        ],
        'acudientes' => [['acudido', 'SELECT ea.id_acudiente AS id, u.id_institucion AS inst FROM estudiante_acudiente ea JOIN estudiantes e ON e.id_estudiante = ea.id_estudiante JOIN usuarios u ON u.id_usuario = e.id_usuario']],
        'eventos' => [
            ['curso del evento', 'SELECT ev.id_evento AS id, c.id_institucion AS inst FROM eventos ev JOIN cursos c ON c.id_curso = ev.id_curso'],
            ['asignación del evento', 'SELECT ev.id_evento AS id, c.id_institucion AS inst FROM eventos ev JOIN asignaciones_academicas a ON a.id_asignacion = ev.id_asignacion JOIN cursos c ON c.id_curso = a.id_curso'],
        ],
    ];

    /** Relaciones padre-hijo dentro del propio catálogo o entre catálogos que deben seguir a su padre. */
    private const JERARQUIAS = [
        ['materias', 'id_materia', 'id_materia_padre', 'materias', 'materia padre'],
        ['materias', 'id_materia', 'id_area', 'areas', 'área'],
        ['colegio_imagenes', 'id_imagen', 'id_configuracion', 'colegio_configuracion', 'configuración'],
    ];

    private const SUPERADMIN = 8;

    private array $instituciones = [];

    private array $registros = [];

    public function __construct(private Connection $db, private ?array $tablasConColumnaForzadas = null) {}

    public function ejecutar(): array
    {
        $this->registros = [];
        $this->instituciones = array_map(fn ($i) => (array) $i, $this->db->select('SELECT id_institucion AS id, slug, nombre, estado FROM instituciones ORDER BY id_institucion'));
        $columnas = $this->columnasB2();
        $auditoria = (new AuditoriaPertenencia($this->db))->ejecutar();

        $usos = $this->usos();
        $this->catalogos($usos);
        $this->areas();
        $this->perfiles();
        $this->configuracion($usos);
        $this->huerfanos($auditoria);
        $this->jerarquias();
        $this->valoresActuales($columnas);

        ksort($this->registros);
        $registros = array_values(array_map(fn ($r) => $this->ordenar($r), $this->registros));
        usort($registros, fn ($a, $b) => [array_search($a['tabla'], array_keys(self::TABLAS)), $a['id']] <=> [array_search($b['tabla'], array_keys(self::TABLAS)), $b['id']]);

        $plan = [
            'esquema' => ['b2_aplicada' => count($columnas) === count(self::TABLAS), 'tablas_con_id_institucion' => $columnas],
            'instituciones' => $this->instituciones,
            'registros' => $registros,
            'clonaciones' => $this->clonaciones($registros, $usos),
            'resumen_por_tabla' => $this->resumenPorTabla($registros),
            'totales' => $this->totales($registros),
            'relaciones_cruzadas' => array_values(array_filter($auditoria['hallazgos'], fn ($h) => $h['seccion'] === 'relaciones_cruzadas')),
            'referencias_huerfanas' => array_values(array_filter($auditoria['hallazgos'], fn ($h) => $h['seccion'] === 'referencias_huerfanas')),
            'configuracion' => $this->hallazgosConfiguracion($auditoria),
            'restricciones_unicas' => $this->unicos(),
            'dependencias_singleton' => self::dependenciasSingleton(),
        ];
        $plan['huella'] = self::huella($plan);

        return $plan;
    }

    /**
     * Huella de los DATOS que sustentan el plan (no del esquema ni del código):
     * instituciones, clasificación de cada registro, referencias a repuntar,
     * relaciones cruzadas y huérfanos. Cambiar código, aplicar B2 o anticipar
     * R2 no la altera; cambiar un dato relevante sí, e invalida la aprobación.
     */
    public static function huella(array $plan): string
    {
        $registros = array_map(fn ($r) => array_intersect_key($r, array_flip(['tabla', 'id', 'descripcion', 'situacion', 'instituciones', 'institucion_propuesta', 'regla', 'usos', 'usos_sin_institucion', 'dependencias', 'valor_actual'])), $plan['registros']);
        $clones = array_map(fn ($c) => array_intersect_key($c, array_flip(['tabla', 'id', 'instituciones', 'referencias_por_institucion', 'referencias_sin_institucion', 'relaciones_padre_hijo'])), $plan['clonaciones']);
        $hallazgos = fn ($l) => array_map(fn ($h) => [$h['tabla'], $h['id'], $h['detalle']], $l);

        return hash('sha256', json_encode([$plan['instituciones'], $registros, $clones, $hallazgos($plan['relaciones_cruzadas']), $hallazgos($plan['referencias_huerfanas'])]));
    }

    // ------------------------------------------------------------------
    private function columnasB2(): array
    {
        if ($this->tablasConColumnaForzadas !== null) {
            return $this->tablasConColumnaForzadas;
        }
        $schema = Schema::connection($this->db->getName());

        return array_values(array_filter(array_keys(self::TABLAS), fn ($t) => $schema->hasColumn($t, 'id_institucion')));
    }

    private function nuevo(string $tabla, int $id, ?string $descripcion): array
    {
        return ['tabla' => $tabla, 'id' => $id, 'descripcion' => $descripcion, 'situacion' => null, 'instituciones' => [], 'accion_propuesta' => null,
            'institucion_propuesta' => null, 'regla' => null, 'justificacion' => null, 'usos' => 0, 'usos_sin_institucion' => 0,
            'dependencias' => [], 'observaciones' => [], 'valor_actual' => null];
    }

    private function filas(string $tabla): array
    {
        [$pk, $desc] = self::TABLAS[$tabla];
        $sqlDesc = str_contains($desc, '(') && $this->db->getDriverName() === 'sqlite' ? "nombre_periodo || ' ' || anio_lectivo" : $desc;

        return $this->db->select("SELECT {$pk} AS id, {$sqlDesc} AS d".($tabla === 'evento_categorias' ? ', es_sistema AS sis' : '')." FROM {$tabla} ORDER BY {$pk}");
    }

    /** @return array<string, array<int, list<array{tabla:string,columna:string,rid:string,inst:?int}>>> */
    private function usos(): array
    {
        $usos = [];
        foreach (self::USOS as $tabla => $consultas) {
            foreach ($consultas as [$refTabla, $refCol, $sql]) {
                foreach ($this->db->select($sql) as $u) {
                    if ($u->id === null) {
                        continue;
                    }
                    $usos[$tabla][(int) $u->id][] = ['tabla' => $refTabla, 'columna' => $refCol, 'rid' => (string) $u->rid, 'inst' => $u->inst === null ? null : (int) $u->inst];
                }
            }
        }
        foreach ($usos as &$porId) {
            foreach ($porId as &$lista) {
                usort($lista, fn ($a, $b) => [$a['tabla'], $a['rid']] <=> [$b['tabla'], $b['rid']]);
            }
        }

        return $usos;
    }

    private function catalogos(array $usos): void
    {
        $unica = count($this->instituciones) === 1 ? (int) $this->instituciones[0]['id'] : null;

        foreach (['materias', 'periodos_academicos', 'tipos_actividad', 'evento_categorias', 'escala_notas'] as $tabla) {
            $plantillaPermitida = self::TABLAS[$tabla][2];
            foreach ($this->filas($tabla) as $f) {
                $r = $this->nuevo($tabla, (int) $f->id, (string) $f->d);
                $lista = $usos[$tabla][(int) $f->id] ?? [];
                $insts = $this->instsDe($lista);
                $r['instituciones'] = $insts;
                $r['usos'] = count($lista);
                $r['usos_sin_institucion'] = count(array_filter($lista, fn ($u) => $u['inst'] === null));
                $r['dependencias'] = $this->resumenRefs($lista);

                if ($tabla === 'escala_notas' || ($tabla === 'evento_categorias' && (int) $f->sis === 1)) {
                    $this->clasificar($r, self::PLANTILLA, 'plantilla', $tabla === 'escala_notas' ? 'escala_global' : 'categoria_de_sistema',
                        $tabla === 'escala_notas' ? 'Escala de notas sin modelo ni uso por institución: plantilla global (id_institucion NULL, sin cambios).' : 'Categoría de sistema (es_sistema = 1): plantilla global; no se modifica.');
                    if ($insts) {
                        $r['observaciones'][] = 'Usada por '.count($insts).' institución(es); como plantilla sigue siendo de solo lectura para todas.';
                    }
                } elseif (count($insts) > 1) {
                    $this->clasificar($r, self::COMPARTIDO, 'clonar', 'usos_en_varias_instituciones', 'Usado por varias instituciones: el diseño propone una copia por institución y repuntar sus referencias. Requiere aprobación.');
                } elseif (count($insts) === 1) {
                    $this->clasificar($r, self::INEQUIVOCA, 'asignar', 'usos_de_una_institucion', 'Todos sus usos con institución pertenecen a la misma.', $insts[0]);
                    if ($r['usos_sin_institucion'] > 0) {
                        $this->clasificar($r, self::MANUAL, 'revisar', 'usos_sin_institucion', "{$r['usos_sin_institucion']} uso(s) sin institución determinable (p. ej. creador SuperAdmin): confirmar la institución.");
                    }
                } elseif ($r['usos_sin_institucion'] > 0) {
                    $this->clasificar($r, self::MANUAL, 'decidir', 'solo_usos_sin_institucion', 'Solo tiene usos sin institución determinable.');
                } elseif ($unica !== null) {
                    $this->clasificar($r, self::INEQUIVOCA, 'asignar', 'unica_institucion_en_la_base', 'Sin uso, pero la base tiene una sola institución.', $unica);
                } else {
                    $this->clasificar($r, self::SIN_PROPIETARIO, 'pendiente', 'sin_uso_varias_instituciones', 'Sin uso y hay varias instituciones: no hay propietario identificable. Requiere decisión.');
                }
                $r['opciones'] = $this->opciones($r['situacion'], $plantillaPermitida);
                $this->registros[$this->clave($tabla, $r['id'])] = $r;
            }
        }
    }

    /** Áreas: siguen a sus materias (según lo inferido para cada materia). */
    private function areas(): void
    {
        $materiasPorArea = [];
        foreach ($this->db->select('SELECT id_materia AS mid, id_area AS aid FROM materias WHERE id_area IS NOT NULL ORDER BY id_materia') as $m) {
            $materiasPorArea[(int) $m->aid][] = (int) $m->mid;
        }
        $unica = count($this->instituciones) === 1 ? (int) $this->instituciones[0]['id'] : null;

        foreach ($this->filas('areas') as $f) {
            $r = $this->nuevo('areas', (int) $f->id, (string) $f->d);
            $insts = [];
            $pendientes = 0;
            foreach ($materiasPorArea[(int) $f->id] ?? [] as $mid) {
                $m = $this->registros[$this->clave('materias', $mid)] ?? null;
                if ($m && $m['instituciones']) {
                    $insts = array_merge($insts, $m['instituciones']);
                } else {
                    $pendientes++;
                }
            }
            $insts = array_values(array_unique($insts));
            sort($insts);
            $r['instituciones'] = $insts;
            $r['usos'] = count($materiasPorArea[(int) $f->id] ?? []);
            $r['usos_sin_institucion'] = $pendientes;
            $r['dependencias'] = $r['usos'] ? [['tabla' => 'materias', 'columna' => 'id_area', 'filas' => $r['usos']]] : [];

            match (true) {
                count($insts) > 1 => $this->clasificar($r, self::COMPARTIDO, 'clonar', 'materias_de_varias_instituciones', 'Sus materias pertenecen a varias instituciones.'),
                count($insts) === 1 => $this->clasificar($r, self::INEQUIVOCA, 'asignar', 'materias_de_una_institucion', 'Sus materias pertenecen a una sola institución.', $insts[0]),
                $unica !== null => $this->clasificar($r, self::INEQUIVOCA, 'asignar', 'unica_institucion_en_la_base', 'Sin uso, pero la base tiene una sola institución.', $unica),
                default => $this->clasificar($r, self::SIN_PROPIETARIO, 'pendiente', 'sin_uso_varias_instituciones', 'Sin materias con institución y hay varias instituciones.'),
            };
            $r['opciones'] = $this->opciones($r['situacion'], false);
            $this->registros[$this->clave('areas', $r['id'])] = $r;
        }
    }

    /** Perfiles, eventos y soportes: institución del usuario dueño + verificación de coherencia. */
    private function perfiles(): void
    {
        foreach (self::DUENO as $tabla => $sql) {
            $evidencia = [];
            foreach (self::EVIDENCIA[$tabla] ?? [] as [$desc, $sqlEv]) {
                foreach ($this->db->select($sqlEv) as $e) {
                    if ($e->inst !== null) {
                        $evidencia[(int) $e->id][(int) $e->inst][$desc] = true;
                    }
                }
            }
            $descripciones = collect($this->filas($tabla))->mapWithKeys(fn ($f) => [(int) $f->id => (string) $f->d]);

            foreach ($this->db->select($sql.' ORDER BY 1') as $f) {
                $r = $this->nuevo($tabla, (int) $f->id, $descripciones[(int) $f->id] ?? null);
                $ev = $evidencia[(int) $f->id] ?? [];
                ksort($ev);
                $r['instituciones'] = array_keys($ev);

                if ($f->existe === null) {
                    $this->clasificar($r, self::HUERFANO, 'revisar', 'usuario_inexistente', "El usuario {$f->uid} no existe.");
                } elseif ($f->inst === null) {
                    $sa = (int) $f->rol === self::SUPERADMIN;
                    $this->clasificar($r, self::MANUAL, 'decidir', $sa ? 'dueno_superadmin' : 'dueno_sin_institucion',
                        ($sa ? 'Creado por el SuperAdmin' : 'Su usuario no tiene institución').(count($ev) ? '; la evidencia apunta a '.implode(', ', array_keys($ev)) : '; sin otra evidencia').'. No se asigna por defecto.');
                } else {
                    $inst = (int) $f->inst;
                    $ajenas = array_diff(array_keys($ev), [$inst]);
                    $r['instituciones'] = array_values(array_unique(array_merge([$inst], array_keys($ev))));
                    sort($r['instituciones']);
                    if ($ajenas) {
                        $detalle = collect($ajenas)->map(fn ($i) => $i.' ('.implode(', ', array_keys($ev[$i])).')')->implode('; ');
                        $this->clasificar($r, self::INCONSISTENTE, 'revisar', 'evidencia_de_otra_institucion', "Su usuario es de {$inst}, pero tiene relación con: {$detalle}.", $inst);
                    } else {
                        $this->clasificar($r, self::INEQUIVOCA, 'asignar', 'institucion_del_usuario', "Institución de su usuario ({$f->uid}).", $inst);
                    }
                }
                $r['opciones'] = $this->opciones($r['situacion'], false);
                $this->registros[$this->clave($tabla, $r['id'])] = $r;
            }
        }
    }

    /** Configuración y landing: nunca automático cuando hay varias instituciones. */
    private function configuracion(array $usos): void
    {
        $n = count($this->instituciones);
        foreach (self::SOLO_CORRESPONDENCIA as $tabla) {
            foreach ($this->filas($tabla) as $f) {
                $r = $this->nuevo($tabla, (int) $f->id, (string) $f->d);
                $r['instituciones'] = array_map(fn ($i) => (int) $i['id'], $this->instituciones);
                if ($tabla === 'colegio_configuracion') {
                    $r['dependencias'] = $this->resumenRefs($usos['colegio_configuracion'][(int) $f->id] ?? []);
                }
                $sit = $n > 1 ? 'compartido_por_'.$n.'_instituciones' : 'una_institucion';
                $this->clasificar($r, self::MANUAL, 'decidir', $sit,
                    $tabla === 'colegio_configuracion'
                        ? "Única configuración del sistema (ColegioConfiguracion::singleton()), visible para {$n} institución(es). Solo el propietario decide a quién pertenece y si se copia."
                        : "Contenido público único, mostrado por igual para {$n} institución(es). Solo por archivo de correspondencia.");
                $r['opciones'] = $tabla === 'colegio_imagenes'
                    ? ['seguir_configuracion', 'asignar', 'clonar', 'descartar', 'pendiente']
                    : ['asignar', 'clonar', 'descartar', 'pendiente'];
                if (in_array($tabla, array_keys(ArchivoLegado::TABLAS), true) && $r['usos'] === 0) {
                    $r['opciones'][] = 'archivar_sin_propietario';
                }
                $this->registros[$this->clave($tabla, $r['id'])] = $r;
            }
        }
    }

    /** Huérfanos detectados por la auditoría (B1) en las tablas que se poblarán. */
    private function huerfanos(array $auditoria): void
    {
        foreach ($auditoria['hallazgos'] as $h) {
            if ($h['seccion'] === 'referencias_huerfanas' && $h['id'] !== null && isset($this->registros[$this->clave($h['tabla'], (int) $h['id'])])) {
                $r = &$this->registros[$this->clave($h['tabla'], (int) $h['id'])];
                $r['observaciones'][] = 'Referencia huérfana: '.$h['detalle'];
                $this->clasificar($r, self::HUERFANO, 'revisar', 'referencia_huerfana', 'Tiene una referencia a un registro inexistente: '.$h['detalle'].'.');
                $r['opciones'] = $this->opciones(self::HUERFANO, false);
                unset($r);
            }
        }
    }

    /** Hijo y padre deben terminar en la misma institución (materia padre, área, configuración). */
    private function jerarquias(): void
    {
        foreach (self::JERARQUIAS as [$tabla, $pk, $col, $padre, $desc]) {
            if ($padre === 'colegio_configuracion') {
                foreach ($this->db->select("SELECT {$pk} AS id, {$col} AS p FROM {$tabla} WHERE {$col} IS NOT NULL") as $f) {
                    if (isset($this->registros[$this->clave($tabla, (int) $f->id)])) {
                        $this->registros[$this->clave($tabla, (int) $f->id)]['dependencias'][] = ['tabla' => $padre, 'columna' => $col, 'padre' => (int) $f->p, 'regla' => 'debe terminar en la misma institución que su configuración'];
                    }
                }

                continue;
            }
            foreach ($this->db->select("SELECT {$pk} AS id, {$col} AS p FROM {$tabla} WHERE {$col} IS NOT NULL ORDER BY {$pk}") as $f) {
                $hijo = &$this->registros[$this->clave($tabla, (int) $f->id)];
                $p = $this->registros[$this->clave($padre, (int) $f->p)] ?? null;
                $hijo['dependencias'][] = ['tabla' => $padre, 'columna' => $col, 'padre' => (int) $f->p];
                if ($p && $hijo['situacion'] === self::INEQUIVOCA && $p['situacion'] === self::INEQUIVOCA && $p['institucion_propuesta'] !== $hijo['institucion_propuesta']) {
                    $this->clasificar($hijo, self::INCONSISTENTE, 'revisar', 'padre_en_otra_institucion', "Su {$desc} ({$f->p}) quedaría en la institución {$p['institucion_propuesta']} y él en {$hijo['institucion_propuesta']}.", $hijo['institucion_propuesta']);
                    $hijo['opciones'] = $this->opciones(self::INCONSISTENTE, false);
                } elseif ($p && $p['situacion'] === self::COMPARTIDO && $hijo['situacion'] === self::INEQUIVOCA) {
                    $hijo['observaciones'][] = "Su {$desc} ({$f->p}) se clonaría: al aplicar debe apuntar a la copia de la institución {$hijo['institucion_propuesta']}.";
                }
                unset($hijo);
            }
        }
    }

    /** Con B2 aplicada: compara el valor actual de id_institucion con lo inferido. */
    private function valoresActuales(array $columnas): void
    {
        foreach ($columnas as $tabla) {
            $pk = self::TABLAS[$tabla][0];
            foreach ($this->db->select("SELECT {$pk} AS id, id_institucion AS inst FROM {$tabla} WHERE id_institucion IS NOT NULL ORDER BY {$pk}") as $f) {
                $r = &$this->registros[$this->clave($tabla, (int) $f->id)];
                $r['valor_actual'] = (int) $f->inst;
                if (in_array($r['situacion'], [self::INCONSISTENTE, self::HUERFANO], true)) {
                    unset($r);   // ya tiene un problema propio: se conserva su motivo original

                    continue;
                }
                $porDecision = in_array($r['situacion'], [self::MANUAL, self::SIN_PROPIETARIO], true)
                    && ($r['instituciones'] === [] || in_array((int) $f->inst, $r['instituciones'], true));
                if ($r['situacion'] === self::INEQUIVOCA && $r['institucion_propuesta'] === (int) $f->inst) {
                    $this->clasificar($r, self::YA_ASIGNADO, 'ninguna', $r['regla'], 'Ya tiene la institución que se inferiría.', (int) $f->inst);
                } elseif ($porDecision) {
                    // Asignado por una decisión humana (no deducible de los usos) y sin evidencia en contra
                    $this->clasificar($r, self::YA_ASIGNADO, 'ninguna', 'asignado_por_decision', 'Tiene una institución asignada por decisión, compatible con sus usos.', (int) $f->inst);
                } else {
                    $this->clasificar($r, self::INCONSISTENTE, 'revisar', 'valor_actual_distinto', "id_institucion actual = {$f->inst}, pero la inferencia da: {$r['situacion']}".($r['institucion_propuesta'] ? " ({$r['institucion_propuesta']})" : '').'.', $r['institucion_propuesta']);
                }
                $r['opciones'] = $this->opciones($r['situacion'], self::TABLAS[$tabla][2]);
                unset($r);
            }
        }
        $this->decisionesSinValor();
    }

    /**
     * plantilla y descartar dejan id_institucion en NULL: lo único que los
     * distingue de «aún sin decidir» es la trazabilidad de un lote vigente.
     */
    private function decisionesSinValor(): void
    {
        if (! Schema::connection($this->db->getName())->hasTable('migracion_pertenencia')) {
            return;
        }
        $filas = $this->db->select("SELECT DISTINCT accion, tabla, id_registro FROM migracion_pertenencia WHERE accion IN ('plantilla', 'descartar') AND revertido_at IS NULL ORDER BY tabla, id_registro");
        foreach ($filas as $f) {
            $k = $this->clave($f->tabla, (int) $f->id_registro);
            if (! isset($this->registros[$k]) || $this->registros[$k]['valor_actual'] !== null || in_array($this->registros[$k]['situacion'], [self::PLANTILLA, self::HUERFANO, self::INCONSISTENTE], true)) {
                continue;
            }
            $r = &$this->registros[$k];
            if ($f->accion === 'plantilla') {
                $this->clasificar($r, self::PLANTILLA, 'plantilla', 'plantilla_por_decision', 'Declarada plantilla global en un lote aplicado.');
            } else {
                $this->clasificar($r, self::YA_ASIGNADO, 'ninguna', 'descartado_por_decision', 'Excluido de la migración por decisión (queda sin institución).');
            }
            $r['opciones'] = ['ninguna'];
            unset($r);
        }
    }

    // ------------------------------------------------------------------
    private function clonaciones(array $registros, array $usos): array
    {
        $unicos = $this->unicos();
        $res = [];
        foreach ($registros as $r) {
            if ($r['situacion'] !== self::COMPARTIDO) {
                continue;
            }
            $porInst = [];
            $sinInst = [];
            if ($r['tabla'] === 'areas') {
                foreach ($this->db->select('SELECT id_materia AS mid FROM materias WHERE id_area = ? ORDER BY id_materia', [$r['id']]) as $m) {
                    $mr = $this->registros[$this->clave('materias', (int) $m->mid)] ?? null;
                    foreach ($mr['instituciones'] ?? [null] as $i) {
                        $i === null ? $sinInst[] = ['tabla' => 'materias', 'columna' => 'id_area', 'rid' => (string) $m->mid] : $porInst[$i][] = ['tabla' => 'materias', 'columna' => 'id_area', 'rid' => (string) $m->mid];
                    }
                }
            } else {
                foreach ($usos[$r['tabla']][$r['id']] ?? [] as $u) {
                    $fila = ['tabla' => $u['tabla'], 'columna' => $u['columna'], 'rid' => $u['rid']];
                    $u['inst'] === null ? $sinInst[] = $fila : $porInst[$u['inst']][] = $fila;
                }
            }
            ksort($porInst);
            $conteo = array_map('count', $porInst);
            $max = $conteo ? max($conteo) : 0;
            $tablaUnicos = array_values(array_filter($unicos, fn ($u) => $u['tabla'] === $r['tabla']));

            $hijos = [];
            if ($r['tabla'] === 'materias') {
                foreach ($this->db->select('SELECT id_materia AS id FROM materias WHERE id_materia_padre = ? ORDER BY id_materia', [$r['id']]) as $h) {
                    $hijos[] = ['tabla' => 'materias', 'columna' => 'id_materia_padre', 'id' => (int) $h->id];
                }
                $area = $this->db->selectOne('SELECT id_area AS a FROM materias WHERE id_materia = ?', [$r['id']])->a ?? null;
                if ($area !== null) {
                    $hijos[] = ['tabla' => 'areas', 'columna' => 'id_area (padre)', 'id' => (int) $area, 'nota' => 'cada copia debe apuntar al área de su institución'];
                }
            }

            $riesgos = [];
            if ($sinInst) {
                $riesgos[] = count($sinInst).' referencia(s) sin institución determinable: no se sabría a qué copia apuntar.';
            }
            foreach ($tablaUnicos as $u) {
                $riesgos[] = 'Único global ('.implode(', ', $u['columnas']).'): una copia con el mismo valor violaría la restricción. Requiere R2 (único compuesto con id_institucion) ANTES de clonar, o renombrar la copia.';
            }
            foreach ($this->reglasBorrado($r['tabla']) as $fk) {
                if ($fk['on_delete'] === 'cascade') {
                    $riesgos[] = "{$fk['tabla']}.{$fk['columna']} ON DELETE CASCADE: si al consolidar se borrara el original sin repuntar, se borrarían en cascada sus {$fk['tabla']}.";
                }
            }

            $res[] = [
                'tabla' => $r['tabla'], 'id' => $r['id'], 'descripcion' => $r['descripcion'],
                'instituciones' => array_keys($porInst),
                'copias_necesarias' => max(0, count($porInst) - 1),
                'nota_copias' => 'El original se conserva para UNA institución (decisión del propietario) y se crea una copia para cada una de las demás.',
                'referencias_por_institucion' => array_map(fn ($lista) => ['total' => count($lista), 'filas' => $lista], $porInst),
                'referencias_sin_institucion' => $sinInst,
                'referencias_a_repuntar_minimo' => array_sum($conteo) - $max,
                'referencias_a_repuntar_maximo' => array_sum($conteo) - ($conteo ? min($conteo) : 0),
                'relaciones_padre_hijo' => $hijos,
                'conflictos_unicos' => $tablaUnicos,
                'riesgos_integridad' => $riesgos,
            ];
        }

        return $res;
    }

    private function reglasBorrado(string $tabla): array
    {
        $schema = Schema::connection($this->db->getName());
        $res = [];
        foreach (['materias' => ['asignaciones_academicas', 'profesor_materia', 'materias'], 'areas' => ['materias'], 'periodos_academicos' => ['actividades', 'boletines'],
            'tipos_actividad' => ['actividades'], 'evento_categorias' => ['eventos'], 'colegio_configuracion' => ['colegio_imagenes']][$tabla] ?? [] as $hija) {
            foreach ($schema->getForeignKeys($hija) as $fk) {
                if ($fk['foreign_table'] === $tabla) {
                    $res[] = ['tabla' => $hija, 'columna' => implode(',', $fk['columns']), 'on_delete' => strtolower((string) $fk['on_delete'])];
                }
            }
        }

        return $res;
    }

    /** Únicos globales (sin id_institucion) de las tablas a poblar. */
    private function unicos(): array
    {
        $schema = Schema::connection($this->db->getName());
        $res = [];
        foreach (array_keys(self::TABLAS) as $tabla) {
            foreach ($schema->getIndexes($tabla) as $i) {
                if ($i['unique'] && ! $i['primary'] && ! in_array('id_institucion', $i['columns'], true) && $i['columns'] !== ['id_usuario']) {
                    $res[] = ['tabla' => $tabla, 'indice' => $i['name'], 'columnas' => $i['columns']];
                }
            }
        }
        usort($res, fn ($a, $b) => [$a['tabla'], $a['indice']] <=> [$b['tabla'], $b['indice']]);

        return $res;
    }

    private function hallazgosConfiguracion(array $auditoria): array
    {
        $res = array_values(array_filter($auditoria['hallazgos'], fn ($h) => $h['seccion'] === 'configuracion'));
        foreach (['colegio_imagenes' => 'imágenes del colegio', 'landing_galeria' => 'fotos de galería', 'landing_noticias' => 'noticias'] as $tabla => $desc) {
            $n = (int) $this->db->selectOne("SELECT COUNT(*) AS n FROM {$tabla}")->n;
            if ($n > 0) {
                $res[] = ['seccion' => 'configuracion', 'severidad' => count($this->instituciones) > 1 ? AuditoriaPertenencia::CRITICO : AuditoriaPertenencia::INFO, 'tabla' => $tabla, 'id' => null,
                    'detalle' => "{$n} {$desc} sin institución, mostradas a ".count($this->instituciones).' institución(es)'];
            }
        }

        return $res;
    }

    /**
     * Archivos que dependen de ColegioConfiguracion::singleton() (lectura de
     * código, no de base). directa = llamada explícita; indirecta = vista que usa
     * $colegioConfiguracion, compartida a todas las vistas por AppServiceProvider.
     * Se omiten comentarios y los archivos de este simulador.
     */
    public static function dependenciasSingleton(): array
    {
        $propios = [__FILE__, (new \ReflectionClass(\App\Modules\Instituciones\Console\Commands\PoblarPertenenciaCommand::class))->getFileName()];
        $res = [];
        foreach ([app_path(), resource_path('views'), base_path('routes')] as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $archivo) {
                if (! str_ends_with($archivo->getFilename(), '.php') || in_array($archivo->getPathname(), $propios, true)) {
                    continue;
                }
                $lineas = ['directa' => [], 'indirecta' => []];
                foreach (file($archivo->getPathname()) as $n => $l) {
                    $t = ltrim($l);
                    if (str_starts_with($t, '*') || str_starts_with($t, '//') || str_starts_with($t, '/*') || str_starts_with($t, '{{--')) {
                        continue;
                    }
                    if (str_contains($l, 'ColegioConfiguracion::singleton(')) {
                        $lineas['directa'][] = $n + 1;
                    } elseif (str_contains($l, '$colegioConfiguracion')) {
                        $lineas['indirecta'][] = $n + 1;
                    }
                }
                foreach (array_filter($lineas) as $tipo => $ls) {
                    $res[] = ['archivo' => ltrim(str_replace(base_path(), '', $archivo->getPathname()), '/'), 'tipo' => $tipo, 'lineas' => $ls];
                }
            }
        }
        usort($res, fn ($a, $b) => [$a['tipo'], $a['archivo']] <=> [$b['tipo'], $b['archivo']]);

        return $res;
    }

    // ------------------------------------------------------------------
    private function clasificar(array &$r, string $situacion, string $accion, string $regla, string $justificacion, ?int $inst = null): void
    {
        $r['situacion'] = $situacion;
        $r['accion_propuesta'] = $accion;
        $r['regla'] = $regla;
        $r['justificacion'] = $justificacion;
        $r['institucion_propuesta'] = in_array($situacion, [self::INEQUIVOCA, self::YA_ASIGNADO], true) ? $inst : null;
        if (in_array($situacion, [self::INCONSISTENTE], true) && $inst !== null) {
            $r['observaciones'][] = "Institución sugerida por la regla principal: {$inst} (no se aplica sin revisión).";
        }
    }

    private function opciones(string $situacion, bool $plantilla): array
    {
        return match ($situacion) {
            self::INEQUIVOCA => ['asignar'],
            self::YA_ASIGNADO, self::PLANTILLA => ['ninguna'],
            self::COMPARTIDO => array_merge(['clonar', 'asignar'], $plantilla ? ['plantilla'] : [], ['pendiente']),
            self::SIN_PROPIETARIO => array_merge(['asignar'], $plantilla ? ['plantilla'] : [], ['clonar', 'pendiente', 'descartar']),
            default => array_merge(['asignar'], $plantilla ? ['plantilla'] : [], ['clonar', 'pendiente', 'descartar']),
        };
    }

    private function instsDe(array $lista): array
    {
        $i = array_values(array_unique(array_filter(array_column($lista, 'inst'), fn ($x) => $x !== null)));
        sort($i);

        return $i;
    }

    private function resumenRefs(array $lista): array
    {
        $res = [];
        foreach ($lista as $u) {
            $k = $u['tabla'].'.'.$u['columna'];
            $res[$k] ??= ['tabla' => $u['tabla'], 'columna' => $u['columna'], 'filas' => 0];
            $res[$k]['filas']++;
        }
        ksort($res);

        return array_values($res);
    }

    private function clave(string $tabla, int $id): string
    {
        return $tabla.'#'.str_pad((string) $id, 12, '0', STR_PAD_LEFT);
    }

    private function ordenar(array $r): array
    {
        sort($r['observaciones']);
        $r['observaciones'] = array_values(array_unique($r['observaciones']));

        return $r;
    }

    private function resumenPorTabla(array $registros): array
    {
        $res = [];
        foreach (array_keys(self::TABLAS) as $t) {
            $res[$t] = array_fill_keys([self::INEQUIVOCA, self::YA_ASIGNADO, self::PLANTILLA, self::COMPARTIDO, self::SIN_PROPIETARIO, self::MANUAL, self::HUERFANO, self::INCONSISTENTE], 0) + ['total' => 0, 'copias' => 0];
        }
        foreach ($registros as $r) {
            $res[$r['tabla']][$r['situacion']]++;
            $res[$r['tabla']]['total']++;
            if ($r['situacion'] === self::COMPARTIDO) {
                $res[$r['tabla']]['copias'] += count($r['instituciones']) - 1;
            }
        }

        return $res;
    }

    private function totales(array $registros): array
    {
        $c = array_count_values(array_column($registros, 'situacion'));

        return [
            'registros' => count($registros),
            'asignables_automaticamente' => $c[self::INEQUIVOCA] ?? 0,
            'ya_asignados' => $c[self::YA_ASIGNADO] ?? 0,
            'plantillas_sin_cambio' => $c[self::PLANTILLA] ?? 0,
            'a_clonar' => $c[self::COMPARTIDO] ?? 0,
            'copias_necesarias' => array_sum(array_map(fn ($r) => $r['situacion'] === self::COMPARTIDO ? count($r['instituciones']) - 1 : 0, $registros)),
            'decisiones_manuales' => count(array_filter($registros, fn ($r) => ! in_array($r['situacion'], [self::INEQUIVOCA, self::YA_ASIGNADO, self::PLANTILLA], true))),
            'huerfanos' => $c[self::HUERFANO] ?? 0,
            'inconsistentes' => $c[self::INCONSISTENTE] ?? 0,
        ];
    }
}
