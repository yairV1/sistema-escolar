<?php

namespace App\Modules\Instituciones\Auditoria;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoría de pertenencia institucional (Etapa 2, bloque B1). SOLO LECTURA:
 * únicamente ejecuta SELECT. El comando la invoca dentro de SoloLectura, que
 * además lo impone en el motor.
 *
 * Devuelve hallazgos [seccion, severidad, tabla, id, detalle] y la clasificación
 * de cada fila de catálogo, base del archivo de correspondencia. NO asigna
 * instituciones ni propone valores por defecto.
 */
class AuditoriaPertenencia
{
    public const CRITICO = 'critico';

    public const ADVERTENCIA = 'advertencia';

    public const INFO = 'info';

    /** Tablas que en la Etapa 2 serán propias de una institución. */
    public const TABLAS_INSTITUCIONALES = [
        'cursos', 'materias', 'areas', 'periodos_academicos', 'tipos_actividad', 'escala_notas', 'evento_categorias',
        'estudiantes', 'profesores', 'acudientes', 'colegio_configuracion', 'colegio_imagenes',
        'landing_contenido', 'landing_galeria', 'landing_noticias', 'eventos', 'soportes', 'solicitudes_admision',
    ];

    /** Identidad: su unicidad global es deliberada (ver diseño, alternativa B). */
    private const UNICOS_DE_IDENTIDAD = ['usuarios' => true, 'acudientes' => ['id_usuario'], 'estudiantes' => ['id_usuario'], 'profesores' => ['id_usuario']];

    private array $hallazgos = [];

    private array $catalogos = [];

    public function __construct(private ConnectionInterface $db) {}

    public function ejecutar(): array
    {
        $this->hallazgos = [];
        $this->catalogos = [];
        $instituciones = $this->db->select('SELECT id_institucion, slug, nombre, estado FROM instituciones ORDER BY id_institucion');

        $this->sinInstitucion();
        $this->relacionesCruzadas();
        $this->catalogos(count($instituciones));
        $this->asignacionesInconsistentes();
        $this->referenciasHuerfanas();
        $this->configuracion(count($instituciones));
        $this->restriccionesUnicas();
        $this->coherenciaPorColumnas();

        return [
            'instituciones' => array_map(fn ($i) => (array) $i, $instituciones),
            'hallazgos' => $this->hallazgos,
            'catalogos' => $this->catalogos,
            'resumen' => $this->resumen(),
        ];
    }

    private function agregar(string $seccion, string $severidad, string $tabla, mixed $id, string $detalle): void
    {
        $this->hallazgos[] = compact('seccion', 'severidad', 'tabla', 'id', 'detalle');
    }

    /** @param  array<int, object>  $filas */
    private function volcar(array $filas, string $seccion, string $severidad, string $tabla, callable $detalle): void
    {
        foreach ($filas as $f) {
            $this->agregar($seccion, $severidad, $tabla, $f->id ?? null, $detalle($f));
        }
    }

    // 1. Registros sin institución --------------------------------------
    private function sinInstitucion(): void
    {
        $s = 'sin_institucion';
        $this->volcar($this->db->select('SELECT id_usuario AS id, id_rol FROM usuarios WHERE id_institucion IS NULL AND (id_rol IS NULL OR id_rol <> 8)'),
            $s, self::CRITICO, 'usuarios', fn ($f) => "Usuario sin institución (rol {$f->id_rol})");
        $this->volcar($this->db->select('SELECT id_curso AS id, nombre_curso FROM cursos WHERE id_institucion IS NULL'),
            $s, self::CRITICO, 'cursos', fn ($f) => "Curso {$f->nombre_curso} sin institución");
        $this->volcar($this->db->select('SELECT id_solicitud AS id FROM solicitudes_admision WHERE id_institucion IS NULL'),
            $s, self::ADVERTENCIA, 'solicitudes_admision', fn () => 'Solicitud de admisión sin institución');
        foreach (['estudiantes' => 'id_estudiante', 'profesores' => 'id_profesor', 'acudientes' => 'id_acudiente'] as $tabla => $pk) {
            $this->volcar($this->db->select("SELECT t.{$pk} AS id FROM {$tabla} t LEFT JOIN usuarios u ON u.id_usuario = t.id_usuario WHERE u.id_institucion IS NULL"),
                $s, self::CRITICO, $tabla, fn () => 'Perfil cuyo usuario no tiene institución (o no existe)');
        }
        $this->volcar($this->db->select('SELECT e.id_evento AS id, u.id_rol FROM eventos e LEFT JOIN usuarios u ON u.id_usuario = e.id_usuario_creador WHERE u.id_institucion IS NULL'),
            $s, self::ADVERTENCIA, 'eventos', fn ($f) => 'Evento cuyo creador no tiene institución'.($f->id_rol == 8 ? ' (SuperAdmin)' : ''));
    }

    // 2. Relaciones entre colegios diferentes ---------------------------
    private function relacionesCruzadas(): void
    {
        $consultas = [
            'asignaciones_academicas' => ['Asignación: curso y profesor de instituciones distintas',
                'SELECT a.id_asignacion AS id FROM asignaciones_academicas a JOIN cursos c ON c.id_curso = a.id_curso JOIN profesores p ON p.id_profesor = a.id_profesor JOIN usuarios u ON u.id_usuario = p.id_usuario WHERE c.id_institucion <> u.id_institucion'],
            'matriculas' => ['Matrícula: curso y estudiante de instituciones distintas',
                'SELECT m.id_matricula AS id FROM matriculas m JOIN cursos c ON c.id_curso = m.id_curso JOIN estudiantes e ON e.id_estudiante = m.id_estudiante JOIN usuarios u ON u.id_usuario = e.id_usuario WHERE c.id_institucion <> u.id_institucion'],
            'cursos' => ['Curso: director de grupo de otra institución',
                'SELECT c.id_curso AS id FROM cursos c JOIN profesores p ON p.id_profesor = c.id_director_grupo JOIN usuarios u ON u.id_usuario = p.id_usuario WHERE c.id_institucion <> u.id_institucion'],
            'estudiante_acudiente' => ['Vínculo estudiante–acudiente entre instituciones',
                'SELECT ea.id AS id FROM estudiante_acudiente ea JOIN estudiantes e ON e.id_estudiante = ea.id_estudiante JOIN usuarios ue ON ue.id_usuario = e.id_usuario JOIN acudientes a ON a.id_acudiente = ea.id_acudiente JOIN usuarios ua ON ua.id_usuario = a.id_usuario WHERE ue.id_institucion <> ua.id_institucion'],
            'observaciones' => ['Observación: estudiante y profesor de instituciones distintas',
                'SELECT o.id_observacion AS id FROM observaciones o JOIN estudiantes e ON e.id_estudiante = o.id_estudiante JOIN usuarios ue ON ue.id_usuario = e.id_usuario JOIN profesores p ON p.id_profesor = o.id_profesor JOIN usuarios up ON up.id_usuario = p.id_usuario WHERE ue.id_institucion <> up.id_institucion'],
            'detalle_notas' => ['Nota: estudiante de otra institución que la actividad',
                'SELECT n.id_nota AS id FROM detalle_notas n JOIN actividades ac ON ac.id_actividad = n.id_actividad JOIN asignaciones_academicas a ON a.id_asignacion = ac.id_asignacion JOIN cursos c ON c.id_curso = a.id_curso JOIN estudiantes e ON e.id_estudiante = n.id_estudiante JOIN usuarios u ON u.id_usuario = e.id_usuario WHERE c.id_institucion <> u.id_institucion'],
            'asistencia' => ['Asistencia: estudiante de otra institución que la asignación',
                'SELECT s.id_asistencia AS id FROM asistencia s JOIN asignaciones_academicas a ON a.id_asignacion = s.id_asignacion JOIN cursos c ON c.id_curso = a.id_curso JOIN estudiantes e ON e.id_estudiante = s.id_estudiante JOIN usuarios u ON u.id_usuario = e.id_usuario WHERE c.id_institucion <> u.id_institucion'],
            'boletin_detalle' => ['Boletín: asignación de otra institución que el estudiante',
                'SELECT bd.id_boletin_detalle AS id FROM boletin_detalle bd JOIN boletines b ON b.id_boletin = bd.id_boletin JOIN estudiantes e ON e.id_estudiante = b.id_estudiante JOIN usuarios u ON u.id_usuario = e.id_usuario JOIN asignaciones_academicas a ON a.id_asignacion = bd.id_asignacion JOIN cursos c ON c.id_curso = a.id_curso WHERE c.id_institucion <> u.id_institucion'],
            'eventos' => ['Evento ligado a un curso de otra institución que su creador',
                'SELECT ev.id_evento AS id FROM eventos ev JOIN usuarios u ON u.id_usuario = ev.id_usuario_creador JOIN cursos c ON c.id_curso = ev.id_curso WHERE c.id_institucion <> u.id_institucion'],
            'evento_participantes' => ['Participante de otra institución que el creador del evento',
                'SELECT ep.id_participante AS id FROM evento_participantes ep JOIN eventos ev ON ev.id_evento = ep.id_evento JOIN usuarios uc ON uc.id_usuario = ev.id_usuario_creador JOIN usuarios up ON up.id_usuario = ep.id_usuario WHERE uc.id_institucion <> up.id_institucion'],
            'notificaciones' => ['Comunicado entre usuarios de instituciones distintas',
                'SELECT n.id_notificacion AS id FROM notificaciones n JOIN usuarios uo ON uo.id_usuario = n.id_usuario_origen JOIN usuarios ud ON ud.id_usuario = n.id_usuario_destino WHERE uo.id_institucion <> ud.id_institucion'],
        ];
        foreach ($consultas as $tabla => [$detalle, $sql]) {
            $this->volcar($this->db->select($sql), 'relaciones_cruzadas', self::CRITICO, $tabla, fn () => $detalle);
        }
    }

    // 3. Catálogos: compartidos, resolubles o ambiguos ------------------
    private function catalogos(int $totalInstituciones): void
    {
        $usos = [
            'materias' => ['id_materia', 'nombre_materia', [
                'SELECT a.id_materia AS id, c.id_institucion AS inst FROM asignaciones_academicas a JOIN cursos c ON c.id_curso = a.id_curso',
                'SELECT pm.id_materia AS id, u.id_institucion AS inst FROM profesor_materia pm JOIN profesores p ON p.id_profesor = pm.id_profesor JOIN usuarios u ON u.id_usuario = p.id_usuario',
            ], null],
            'periodos_academicos' => ['id_periodo', 'nombre_periodo', [
                'SELECT ac.id_periodo AS id, c.id_institucion AS inst FROM actividades ac JOIN asignaciones_academicas a ON a.id_asignacion = ac.id_asignacion JOIN cursos c ON c.id_curso = a.id_curso',
                'SELECT b.id_periodo AS id, u.id_institucion AS inst FROM boletines b JOIN estudiantes e ON e.id_estudiante = b.id_estudiante JOIN usuarios u ON u.id_usuario = e.id_usuario',
            ], null],
            'tipos_actividad' => ['id_tipo_actividad', 'nombre_tipo', [
                'SELECT ac.id_tipo_actividad AS id, c.id_institucion AS inst FROM actividades ac JOIN asignaciones_academicas a ON a.id_asignacion = ac.id_asignacion JOIN cursos c ON c.id_curso = a.id_curso',
            ], null],
            'areas' => ['id_area', 'nombre_area', [
                'SELECT m.id_area AS id, c.id_institucion AS inst FROM materias m JOIN asignaciones_academicas a ON a.id_materia = m.id_materia JOIN cursos c ON c.id_curso = a.id_curso WHERE m.id_area IS NOT NULL',
            ], null],
            'evento_categorias' => ['id_categoria', 'nombre', [
                'SELECT ev.id_categoria AS id, u.id_institucion AS inst FROM eventos ev JOIN usuarios u ON u.id_usuario = ev.id_usuario_creador',
            ], 'es_sistema = 1'],
            'escala_notas' => ['id_escala', 'etiqueta', [], '1 = 1'],
        ];

        foreach ($usos as $tabla => [$pk, $nombre, $consultas, $plantilla]) {
            $instituciones = [];
            foreach ($consultas as $sql) {
                foreach ($this->db->select($sql) as $u) {
                    if ($u->inst !== null) {
                        $instituciones[$u->id][(int) $u->inst] = true;
                    }
                }
            }
            $esPlantilla = $plantilla ? collect($this->db->select("SELECT {$pk} AS id FROM {$tabla} WHERE {$plantilla}"))->pluck('id')->map(fn ($i) => (int) $i)->all() : [];
            $esPlantilla = array_merge($esPlantilla, $this->decididos($tabla, ['plantilla', 'descartar']));
            $asignado = $this->tieneColumna($tabla)
                ? collect($this->db->select("SELECT {$pk} AS id, id_institucion AS i FROM {$tabla} WHERE id_institucion IS NOT NULL"))->mapWithKeys(fn ($f) => [(int) $f->id => (int) $f->i])->all()
                : [];

            foreach ($this->db->select("SELECT {$pk} AS id, {$nombre} AS nombre FROM {$tabla} ORDER BY {$pk}") as $fila) {
                $insts = array_keys($instituciones[$fila->id] ?? []);
                sort($insts);
                $valor = $asignado[(int) $fila->id] ?? null;
                $clase = match (true) {
                    $valor !== null && array_diff($insts, [$valor]) === [] => 'asignado',
                    $valor !== null => 'asignado_incoherente',
                    in_array((int) $fila->id, $esPlantilla, true) => 'plantilla',
                    count($insts) > 1 => 'compartido',
                    count($insts) === 1 => 'resoluble',
                    $totalInstituciones <= 1 => 'sin_uso_institucion_unica',
                    default => 'sin_uso_ambiguo',
                };
                $this->catalogos[] = ['tabla' => $tabla, 'id' => (int) $fila->id, 'nombre' => $fila->nombre, 'clase' => $clase, 'instituciones' => $insts];

                $sev = ['compartido' => self::CRITICO, 'sin_uso_ambiguo' => self::ADVERTENCIA, 'asignado_incoherente' => self::CRITICO][$clase] ?? null;
                if ($clase === 'asignado_incoherente') {
                    $this->agregar('catalogos', $sev, $tabla, (int) $fila->id, "«{$fila->nombre}»: asignado a {$valor} pero usado por ".implode(', ', $insts));
                } elseif ($sev) {
                    $this->agregar('catalogos', $sev, $tabla, (int) $fila->id, "«{$fila->nombre}»: ".($clase === 'compartido'
                        ? 'usado por las instituciones '.implode(', ', $insts).' (requiere decisión: clonar)'
                        : 'sin uso y hay varias instituciones (requiere decisión manual)'));
                }
            }
        }
    }

    // 4. Asignaciones inconsistentes --------------------------------------
    private function asignacionesInconsistentes(): void
    {
        $s = 'asignaciones_inconsistentes';
        $this->volcar($this->db->select('SELECT a.id_asignacion AS id, a.anio_lectivo AS aa, c.anio_lectivo AS ac FROM asignaciones_academicas a JOIN cursos c ON c.id_curso = a.id_curso WHERE a.anio_lectivo <> c.anio_lectivo'),
            $s, self::ADVERTENCIA, 'asignaciones_academicas', fn ($f) => "Año de la asignación ({$f->aa}) distinto del año del curso ({$f->ac})");
        $this->volcar($this->db->select('SELECT a.id_asignacion AS id FROM asignaciones_academicas a LEFT JOIN profesor_materia pm ON pm.id_profesor = a.id_profesor AND pm.id_materia = a.id_materia WHERE pm.id_profesor IS NULL'),
            $s, self::INFO, 'asignaciones_academicas', fn () => 'El profesor no tiene registrada la materia en profesor_materia');
        $this->volcar($this->db->select('SELECT ac.id_actividad AS id FROM actividades ac JOIN asignaciones_academicas a ON a.id_asignacion = ac.id_asignacion JOIN periodos_academicos p ON p.id_periodo = ac.id_periodo WHERE p.anio_lectivo <> a.anio_lectivo'),
            $s, self::ADVERTENCIA, 'actividades', fn () => 'Actividad en un periodo de otro año lectivo que su asignación');
    }

    // 5. Referencias huérfanas (por si se importaron datos sin FK) -------
    private function referenciasHuerfanas(): void
    {
        $refs = [
            ['usuarios', 'id_usuario', 'id_institucion', 'instituciones', 'id_institucion'],
            ['usuarios', 'id_usuario', 'id_rol', 'roles', 'id_rol'],
            ['cursos', 'id_curso', 'id_institucion', 'instituciones', 'id_institucion'],
            ['estudiantes', 'id_estudiante', 'id_usuario', 'usuarios', 'id_usuario'],
            ['profesores', 'id_profesor', 'id_usuario', 'usuarios', 'id_usuario'],
            ['acudientes', 'id_acudiente', 'id_usuario', 'usuarios', 'id_usuario'],
            ['matriculas', 'id_matricula', 'id_estudiante', 'estudiantes', 'id_estudiante'],
            ['matriculas', 'id_matricula', 'id_curso', 'cursos', 'id_curso'],
            ['asignaciones_academicas', 'id_asignacion', 'id_curso', 'cursos', 'id_curso'],
            ['asignaciones_academicas', 'id_asignacion', 'id_profesor', 'profesores', 'id_profesor'],
            ['asignaciones_academicas', 'id_asignacion', 'id_materia', 'materias', 'id_materia'],
            ['horarios', 'id_horario', 'id_asignacion', 'asignaciones_academicas', 'id_asignacion'],
            ['actividades', 'id_actividad', 'id_asignacion', 'asignaciones_academicas', 'id_asignacion'],
            ['actividades', 'id_actividad', 'id_periodo', 'periodos_academicos', 'id_periodo'],
            ['actividades', 'id_actividad', 'id_tipo_actividad', 'tipos_actividad', 'id_tipo_actividad'],
            ['detalle_notas', 'id_nota', 'id_actividad', 'actividades', 'id_actividad'],
            ['detalle_notas', 'id_nota', 'id_estudiante', 'estudiantes', 'id_estudiante'],
            ['asistencia', 'id_asistencia', 'id_estudiante', 'estudiantes', 'id_estudiante'],
            ['asistencia', 'id_asistencia', 'id_asignacion', 'asignaciones_academicas', 'id_asignacion'],
            ['observaciones', 'id_observacion', 'id_estudiante', 'estudiantes', 'id_estudiante'],
            ['observaciones', 'id_observacion', 'id_profesor', 'profesores', 'id_profesor'],
            ['boletines', 'id_boletin', 'id_estudiante', 'estudiantes', 'id_estudiante'],
            ['boletines', 'id_boletin', 'id_periodo', 'periodos_academicos', 'id_periodo'],
            ['boletin_detalle', 'id_boletin_detalle', 'id_boletin', 'boletines', 'id_boletin'],
            ['estudiante_acudiente', 'id', 'id_estudiante', 'estudiantes', 'id_estudiante'],
            ['estudiante_acudiente', 'id', 'id_acudiente', 'acudientes', 'id_acudiente'],
            ['eventos', 'id_evento', 'id_usuario_creador', 'usuarios', 'id_usuario'],
            ['eventos', 'id_evento', 'id_categoria', 'evento_categorias', 'id_categoria'],
            ['colegio_imagenes', 'id_imagen', 'id_configuracion', 'colegio_configuracion', 'id_configuracion'],
            ['solicitudes_admision', 'id_solicitud', 'id_institucion', 'instituciones', 'id_institucion'],
        ];
        foreach ($refs as [$tabla, $pk, $col, $refTabla, $refCol]) {
            $this->volcar($this->db->select("SELECT t.{$pk} AS id, t.{$col} AS ref FROM {$tabla} t LEFT JOIN {$refTabla} r ON r.{$refCol} = t.{$col} WHERE t.{$col} IS NOT NULL AND r.{$refCol} IS NULL"),
                'referencias_huerfanas', self::CRITICO, $tabla, fn ($f) => "{$col} = {$f->ref} no existe en {$refTabla}");
        }
    }

    // 6. Configuración institucional duplicada o compartida -------------
    private function configuracion(int $totalInstituciones): void
    {
        $s = 'configuracion';
        $configs = $this->db->select('SELECT id_configuracion AS id, nombre_colegio, logo FROM colegio_configuracion ORDER BY id_configuracion');
        if (count($configs) > 1) {
            $this->agregar($s, self::ADVERTENCIA, 'colegio_configuracion', null, count($configs).' filas de configuración, pero el código usa una sola (singleton): las demás se ignoran');
        }
        if ($this->tieneColumna('colegio_configuracion')) {
            // Con B2: lo crítico es lo que sigue sin dueño; y cada colegio debería tener la suya.
            $sinDueno = (int) $this->db->selectOne('SELECT COUNT(*) AS n FROM colegio_configuracion WHERE id_institucion IS NULL')->n;
            if ($sinDueno > 0 && $totalInstituciones > 1) {
                $this->agregar($s, self::CRITICO, 'colegio_configuracion', null, "{$sinDueno} configuración(es) sin institución, compartidas por {$totalInstituciones} instituciones");
            }
            $sinPropia = $this->db->select('SELECT i.id_institucion AS id FROM instituciones i WHERE NOT EXISTS (SELECT 1 FROM colegio_configuracion c WHERE c.id_institucion = i.id_institucion) ORDER BY 1');
            if ($sinDueno < count($configs)) {
                $this->volcar($sinPropia, $s, self::ADVERTENCIA, 'instituciones', fn () => 'Institución sin configuración propia');
            }
        } elseif (count($configs) >= 1 && $totalInstituciones > 1) {
            $this->agregar($s, self::CRITICO, 'colegio_configuracion', $configs[0]->id, "Una sola configuración compartida por {$totalInstituciones} instituciones (nombre, logo, contacto)");
        }
        $condicion = $this->tieneColumna('landing_contenido') ? ' WHERE id_institucion IS NULL' : '';
        $claves = (int) ($this->db->selectOne('SELECT COUNT(*) AS n FROM landing_contenido'.$condicion)->n ?? 0);
        if ($claves > 0 && $totalInstituciones > 1) {
            $this->agregar($s, self::CRITICO, 'landing_contenido', null, $condicion
                ? "{$claves} clave(s) de la landing sin institución, mostradas a {$totalInstituciones} instituciones"
                : "Una sola landing ({$claves} claves, galería y noticias) compartida por {$totalInstituciones} instituciones");
        }
        foreach ($this->db->select('SELECT nombre, COUNT(*) AS n FROM instituciones GROUP BY nombre HAVING COUNT(*) > 1') as $f) {
            $this->agregar($s, self::ADVERTENCIA, 'instituciones', null, "Nombre de institución repetido: «{$f->nombre}» ({$f->n} veces)");
        }
        if (count($configs) === 1 && $totalInstituciones >= 1) {
            foreach ($this->db->select('SELECT id_institucion AS id, nombre, logo FROM instituciones') as $i) {
                if ($i->nombre !== $configs[0]->nombre_colegio || $i->logo !== $configs[0]->logo) {
                    $this->agregar($s, self::INFO, 'instituciones', $i->id, 'Nombre/logo distintos entre instituciones y colegio_configuracion (dos fuentes de verdad)');
                }
            }
        }
    }

    // 7. Restricciones únicas incompatibles con varias instituciones -----
    private function restriccionesUnicas(): void
    {
        foreach (self::TABLAS_INSTITUCIONALES as $tabla) {
            if (! Schema::connection($this->db->getName())->hasTable($tabla)) {
                continue;
            }
            foreach (Schema::connection($this->db->getName())->getIndexes($tabla) as $indice) {
                if (! $indice['unique'] || $indice['primary'] || in_array('id_institucion', $indice['columns'], true)) {
                    continue;
                }
                $identidad = self::UNICOS_DE_IDENTIDAD[$tabla] ?? null;
                if ($identidad === true || $identidad === $indice['columns']) {
                    continue;
                }
                $this->agregar('restricciones_unicas', self::ADVERTENCIA, $tabla, null,
                    'Único global ('.implode(', ', $indice['columns']).'): impide repetir el valor entre instituciones');
            }
        }
        foreach (['correo', 'numero_documento'] as $col) {
            $this->agregar('restricciones_unicas', self::INFO, 'usuarios', null, "Único global ({$col}): identidad personal, se conserva (ver alternativas de membresía)");
        }
    }

    // 8. Coherencia por las columnas id_institucion (B2 en adelante) ------
    private function coherenciaPorColumnas(): void
    {
        foreach (self::TABLAS_INSTITUCIONALES as $t) {
            if (! $this->tieneColumna($t)) {
                return;   // B2 no aplicada (o incompleta): la coherencia se audita por relaciones
            }
        }
        foreach ((new \App\Modules\Instituciones\Poblacion\VerificadorPertenencia($this->db))->violaciones() as $v) {
            $this->agregar('coherencia_institucional', self::CRITICO, $v['tabla'], $v['id'], $v['regla']);
        }
    }

    private function tieneColumna(string $tabla): bool
    {
        return Schema::connection($this->db->getName())->hasColumn($tabla, 'id_institucion');
    }

    /** ids marcados plantilla/descartar en un lote vigente de migracion_pertenencia. */
    private function decididos(string $tabla, array $acciones): array
    {
        if (! Schema::connection($this->db->getName())->hasTable('migracion_pertenencia')) {
            return [];
        }

        return collect($this->db->table('migracion_pertenencia')->where('tabla', $tabla)->whereIn('accion', $acciones)->whereNull('revertido_at')->pluck('id_registro'))->map(fn ($i) => (int) $i)->all();
    }

    private function resumen(): array
    {
        $r = [];
        foreach ($this->hallazgos as $h) {
            $r[$h['seccion']][$h['severidad']] = ($r[$h['seccion']][$h['severidad']] ?? 0) + 1;
        }
        $r['catalogos_por_clase'] = array_count_values(array_column($this->catalogos, 'clase'));

        return $r;
    }
}
