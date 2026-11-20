<?php

namespace App\Modules\Instituciones\Poblacion;

use App\Modules\Instituciones\Poblacion\SimuladorPoblacion as Sim;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Schema;

/**
 * Archivo de correspondencia institucional (v3): decisiones humanas sobre la
 * pertenencia, individuales o por grupo. Genera la plantilla (todo en null) y
 * valida/resuelve un archivo completado contra una simulación NUEVA.
 *
 * Nunca completa decisiones: si falta una, el archivo se rechaza entero.
 */
class Correspondencia
{
    public const FORMATO = 'correspondencia-institucional/v4';

    private const FORMATOS_ANTERIORES = ['correspondencia-institucional/v3'];

    /** grupo => [tabla, situaciones que agrupa (null = todas las pendientes de la tabla), descripción] */
    public const GRUPOS = [
        'configuracion' => ['colegio_configuracion', null, 'Configuración institucional (nombre, logo, contacto)'],
        'imagenes' => ['colegio_imagenes', null, 'Imágenes del colegio (dependen de la configuración)'],
        'landing' => ['landing_contenido', null, 'Textos de la página pública'],
        'galeria' => ['landing_galeria', null, 'Galería de la página pública'],
        'noticias' => ['landing_noticias', null, 'Noticias de la página pública'],
        'categorias_calendario' => ['evento_categorias', [Sim::SIN_PROPIETARIO], 'Categorías del calendario sin uso'],
    ];

    /** Acciones válidas por tabla (además de las restricciones por situación). */
    private const CLONABLES = ['materias', 'areas', 'periodos_academicos', 'tipos_actividad', 'evento_categorias', 'escala_notas',
        'colegio_configuracion', 'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias'];

    private const PLANTILLA_PERMITIDA = ['evento_categorias', 'escala_notas'];

    private const NO_DECIDIBLES = [Sim::INEQUIVOCA, Sim::YA_ASIGNADO, Sim::PLANTILLA];

    // ---------------------------------------------------------------- generar
    public static function generar(array $plan, string $base): array
    {
        $slug = self::slugs($plan);
        $clon = collect($plan['clonaciones'])->keyBy(fn ($c) => $c['tabla'].'#'.$c['id']);
        $decisiones = [];
        $grupos = [];
        foreach ($plan['registros'] as $r) {
            if (in_array($r['situacion'], self::NO_DECIDIBLES, true)) {
                continue;
            }
            $g = self::grupoDe($r);
            $c = $clon[$r['tabla'].'#'.$r['id']] ?? null;
            $decisiones[] = [
                'tabla' => $r['tabla'], 'id' => $r['id'], 'descripcion' => $r['descripcion'], 'grupo' => $g,
                'situacion' => $r['situacion'], 'regla' => $r['regla'],
                'instituciones_candidatas' => array_map(fn ($i) => $slug[$i] ?? (string) $i, $r['instituciones']),
                'accion_propuesta' => $r['accion_propuesta'], 'opciones' => $r['opciones'] ?? [],
                'decision' => null, 'institucion_destino' => null, 'instituciones_clon' => [], 'conserva_original' => null,
                'justificacion' => $r['justificacion'], 'dependencias' => $r['dependencias'], 'observaciones' => $r['observaciones'],
                'clonacion' => $c ? ['copias_necesarias' => $c['copias_necesarias'], 'referencias_a_repuntar' => [$c['referencias_a_repuntar_minimo'], $c['referencias_a_repuntar_maximo']], 'riesgos' => $c['riesgos_integridad']] : null,
            ];
            if ($g) {
                $grupos[$g] ??= ['grupo' => $g, 'descripcion' => self::GRUPOS[$g][2], 'tabla' => $r['tabla'], 'registros' => [], 'opciones' => null,
                    'decision' => null, 'institucion_destino' => null, 'instituciones_clon' => [], 'conserva_original' => null];
                $grupos[$g]['registros'][] = ['id' => $r['id'], 'descripcion' => $r['descripcion']];
                $op = $r['opciones'] ?? [];
                $grupos[$g]['opciones'] = $grupos[$g]['opciones'] === null ? $op : array_values(array_intersect($grupos[$g]['opciones'], $op));
            }
        }

        return [
            'formato' => self::FORMATO,
            'base' => $base,
            'huella_plan' => $plan['huella'],
            'estado' => 'PENDIENTE DE APROBACIÓN',
            'instrucciones' => 'Cada registro se decide en su entrada de "decisiones" o, si su "decision" es null, con la del "grupo" al que pertenece. '
                .'Acciones: asignar (institucion_destino = slug) | clonar (instituciones_clon = [slugs que tendrán su versión], conserva_original = slug de esa lista que se queda con el registro original) | '
                .'plantilla (solo evento_categorias/escala_notas) | seguir_configuracion (solo imágenes) | descartar (solo registros sin uso; queda sin institución) | '
                .'archivar_sin_propietario (solo registros legados permitidos, sin uso ni institución; conserva payload y checksum, retira la fila operativa). '
                .'"pendiente" o null impiden aplicar: el archivo se rechaza entero. '
                .'Para aplicar se exige además "aprobacion": {"ambito": "pruebas", "base": <base>, "huella_plan": <la misma huella>, "aprobado_por": <nombre>, "fecha": <ISO-8601>}; en esta etapa solo existe el ámbito "pruebas" (colegio_tests). Si "huella_plan" no coincide con una nueva simulación, regenerar. Ver docs/seguridad/etapa2-b4.md.',
            'aprobacion' => null,
            'instituciones' => array_map(fn ($i) => ['id' => $i['id'], 'slug' => $i['slug'], 'nombre' => $i['nombre']], $plan['instituciones']),
            'asignaciones_automaticas' => count(array_filter($plan['registros'], fn ($r) => $r['situacion'] === Sim::INEQUIVOCA)),
            'grupos' => array_values($grupos),
            'decisiones' => $decisiones,
        ];
    }

    private static function grupoDe(array $r): ?string
    {
        foreach (self::GRUPOS as $g => [$tabla, $situaciones]) {
            if ($r['tabla'] === $tabla && ($situaciones === null || in_array($r['situacion'], $situaciones, true))) {
                return $g;
            }
        }

        return null;
    }

    private static function slugs(array $plan): array
    {
        return collect($plan['instituciones'])->mapWithKeys(fn ($i) => [(int) $i['id'] => $i['slug']])->all();
    }

    // --------------------------------------------------------------- resolver
    /**
     * Valida el archivo contra el plan recién simulado y devuelve las
     * operaciones a aplicar (automáticas + decididas), o la lista de errores.
     *
     * @return array{0: array<string, array>, 1: list<string>}
     */
    public static function resolver(array $archivo, array $plan, Connection $db): array
    {
        $errores = [];
        $formato = $archivo['formato'] ?? null;
        if ($formato !== self::FORMATO && ! in_array($formato, self::FORMATOS_ANTERIORES, true)) {
            return [[], ['Formato no soportado: se espera '.self::FORMATO.'.']];
        }
        if (($archivo['huella_plan'] ?? null) !== $plan['huella']) {
            return [[], ['Huella desactualizada: los datos cambiaron desde que se generó el archivo ('.substr((string) ($archivo['huella_plan'] ?? '—'), 0, 12).' ≠ '.substr($plan['huella'], 0, 12).'). Simular y aprobar de nuevo.']];
        }
        if (($archivo['base'] ?? null) !== $db->getDatabaseName()) {
            $errores[] = "El archivo es de la base «".($archivo['base'] ?? '—')."», no de «{$db->getDatabaseName()}».";
        }
        foreach (self::erroresAprobacion($archivo, $db) as $e) {
            $errores[] = $e;
        }
        foreach ($plan['registros'] as $r) {
            if (in_array($r['situacion'], [Sim::HUERFANO, Sim::INCONSISTENTE], true)) {
                $errores[] = "{$r['tabla']}#{$r['id']}: {$r['situacion']} ({$r['regla']}). Corregir los datos antes de poblar.";
            }
        }
        if ($plan['relaciones_cruzadas'] || $plan['referencias_huerfanas']) {
            $errores[] = count($plan['relaciones_cruzadas']).' relación(es) entre colegios y '.count($plan['referencias_huerfanas']).' referencia(s) huérfana(s): la integridad debe corregirse antes de poblar.';
        }

        $porSlug = collect($plan['instituciones'])->mapWithKeys(fn ($i) => [$i['slug'] => (int) $i['id']])->all();
        $grupos = collect($archivo['grupos'] ?? [])->keyBy('grupo');
        $entradas = collect($archivo['decisiones'] ?? [])->keyBy(fn ($d) => ($d['tabla'] ?? '?').'#'.($d['id'] ?? '?'));
        $enPlan = [];
        $ops = [];

        foreach ($plan['registros'] as $r) {
            $k = $r['tabla'].'#'.$r['id'];
            $enPlan[$k] = true;
            if ($r['situacion'] === Sim::INEQUIVOCA) {
                $ops[$k] = ['tabla' => $r['tabla'], 'id' => $r['id'], 'accion' => 'asignar', 'destino' => $r['institucion_propuesta'], 'clones' => [], 'conserva' => null, 'origen' => 'regla', 'regla' => $r['regla']];

                continue;
            }
            if ($r['situacion'] === Sim::PLANTILLA) {
                $ops[$k] = ['tabla' => $r['tabla'], 'id' => $r['id'], 'accion' => 'plantilla', 'destino' => null, 'clones' => [], 'conserva' => null, 'origen' => 'regla', 'regla' => $r['regla']];

                continue;
            }
            if (in_array($r['situacion'], [Sim::YA_ASIGNADO, Sim::HUERFANO, Sim::INCONSISTENTE], true)) {
                continue;
            }

            $e = $entradas[$k] ?? null;
            $g = $e['grupo'] ?? self::grupoDe($r);
            $fuente = ($e['decision'] ?? null) !== null ? $e : ($g ? ($grupos[$g] ?? null) : null);
            $origenRegla = ($e['decision'] ?? null) !== null ? 'archivo:individual' : ($g ? "archivo:grupo:{$g}" : 'archivo');
            if (! $e) {
                $errores[] = "{$k}: falta en el archivo.";

                continue;
            }
            if (! $fuente || ($fuente['decision'] ?? null) === null) {
                $errores[] = "{$k}: sin decisión (ni individual ni de grupo).";

                continue;
            }
            if (($fuente['decision'] ?? null) === 'archivar_sin_propietario' && $formato !== self::FORMATO) {
                $errores[] = "{$k}: archivar_sin_propietario requiere el formato ".self::FORMATO.'.';

                continue;
            }
            $op = self::validar($r, $fuente, $porSlug, $db, $errores);
            if ($op) {
                $ops[$k] = $op + ['origen' => 'correspondencia', 'regla' => $origenRegla];
            }
        }
        foreach ($entradas->keys() as $k) {
            if (! isset($enPlan[$k])) {
                $errores[] = "{$k}: está en el archivo pero no en la simulación actual.";
            }
        }
        // Imágenes que siguen a su configuración: la configuración debe tener destino.
        foreach ($ops as $k => $op) {
            if ($op['accion'] === 'seguir_configuracion') {
                $cfg = collect($plan['registros'])->first(fn ($r) => $r['tabla'] === 'colegio_imagenes' && $r['id'] === $op['id'])['dependencias'] ?? [];
                $idCfg = collect($cfg)->firstWhere('tabla', 'colegio_configuracion')['padre'] ?? null;
                $opCfg = $ops['colegio_configuracion#'.$idCfg] ?? null;
                if (! $opCfg || ! in_array($opCfg['accion'], ['asignar', 'clonar'], true)) {
                    $errores[] = "{$k}: seguir_configuracion exige que la configuración {$idCfg} se asigne o se clone.";
                }
            }
        }
        self::validarArchivosLegado($ops, $plan, $errores);

        return [$errores ? [] : $ops, $errores];
    }

    private static function validarArchivosLegado(array $ops, array $plan, array &$errores): void
    {
        $archivo = array_filter($ops, fn ($op) => $op['accion'] === 'archivar_sin_propietario');
        if ($archivo === []) {
            return;
        }

        foreach (array_keys($archivo) as $k) {
            if (! Schema::hasTable('migracion_legado_archivos')) {
                $errores[] = "{$k}: requiere la migración B10 del archivo legado en la copia aislada.";
                break;
            }
        }

        $registros = collect($plan['registros'])->keyBy(fn ($r) => $r['tabla'].'#'.$r['id']);
        foreach ($archivo as $k => $op) {
            if ($op['tabla'] === 'colegio_configuracion') {
                $hijas = collect($plan['registros'])->filter(function ($r) use ($op) {
                    if ($r['tabla'] !== 'colegio_imagenes') {
                        return false;
                    }

                    return collect($r['dependencias'])->contains(fn ($d) => $d['tabla'] === 'colegio_configuracion' && (int) $d['padre'] === $op['id']);
                });
                foreach ($hijas as $hija) {
                    $hijaKey = 'colegio_imagenes#'.$hija['id'];
                    if (($ops[$hijaKey]['accion'] ?? null) !== 'archivar_sin_propietario') {
                        $errores[] = "{$k}: todas sus imágenes deben incluirse en el mismo archivo legado ({$hijaKey}).";
                    }
                }
            }
            if ($op['tabla'] === 'colegio_imagenes') {
                $dep = collect($registros[$k]['dependencias'] ?? [])->firstWhere('tabla', 'colegio_configuracion');
                $padreKey = 'colegio_configuracion#'.($dep['padre'] ?? 0);
                if (($ops[$padreKey]['accion'] ?? null) !== 'archivar_sin_propietario') {
                    $errores[] = "{$k}: solo se archiva junto con su configuración ({$padreKey}).";
                }
            }
        }
    }

    /**
     * Aprobación explícita: el archivo debe declarar para qué base y ámbito fue
     * aprobado, por quién, y para QUÉ huella. Sin ella, nada se aplica. En B4
     * solo existe el ámbito «pruebas» (la aplicación está limitada a colegio_tests).
     */
    public static function erroresAprobacion(array $archivo, Connection $db): array
    {
        $a = $archivo['aprobacion'] ?? null;
        if (! is_array($a)) {
            return ['Falta "aprobacion": el archivo no está aprobado explícitamente.'];
        }
        $e = [];
        if (($a['ambito'] ?? null) !== 'pruebas') {
            $e[] = 'aprobacion.ambito debe ser "pruebas" (única aplicación habilitada en B4).';
        }
        if (($a['base'] ?? null) !== $db->getDatabaseName()) {
            $e[] = 'aprobacion.base («'.($a['base'] ?? '—')."») no es la base efectiva «{$db->getDatabaseName()}».";
        }
        if (($a['huella_plan'] ?? null) !== ($archivo['huella_plan'] ?? false)) {
            $e[] = 'aprobacion.huella_plan no coincide con la huella del archivo: la aprobación no corresponde a este plan.';
        }
        if (trim((string) ($a['aprobado_por'] ?? '')) === '' || strtotime((string) ($a['fecha'] ?? '')) === false) {
            $e[] = 'aprobacion requiere "aprobado_por" y una "fecha" válida.';
        }

        return $e;
    }

    private static function validar(array $r, array $d, array $porSlug, Connection $db, array &$errores): ?array
    {
        $k = $r['tabla'].'#'.$r['id'];
        $accion = $d['decision'];
        $usos = $r['instituciones'];
        $sinUso = ($r['usos'] ?? 0) === 0;
        $op = ['tabla' => $r['tabla'], 'id' => $r['id'], 'accion' => $accion, 'destino' => null, 'clones' => [], 'conserva' => null];
        $opciones = $r['opciones'] ?? [];
        $error = function (string $m) use (&$errores, $k) {
            $errores[] = "{$k}: {$m}";

            return null;
        };

        if ($accion === 'pendiente') {
            return $error('decisión «pendiente»: no se aplica nada hasta resolverla.');
        }
        if (! in_array($accion, $opciones, true)) {
            return $error("«{$accion}» no es una opción válida (".implode(', ', $opciones).').');
        }
        // Los compartidos y la configuración/landing con usos reales solo pueden repartirse explícitamente.
        $esCompartido = $r['situacion'] === Sim::COMPARTIDO;

        switch ($accion) {
            case 'asignar':
                $dest = $porSlug[$d['institucion_destino'] ?? ''] ?? null;
                if (! $dest) {
                    return $error('asignar requiere institucion_destino con un slug existente.');
                }
                if ($esCompartido || array_diff($usos, [$dest]) && $r['situacion'] !== Sim::MANUAL) {
                    return $error('asignar a una sola institución dejaría referencias de otras: usar clonar.');
                }
                $op['destino'] = $dest;
                break;
            case 'clonar':
                if (! in_array($r['tabla'], self::CLONABLES, true)) {
                    return $error('esta tabla no admite clonación (perfiles, eventos y soportes pertenecen a su usuario).');
                }
                $slugs = array_values(array_unique($d['instituciones_clon'] ?? []));
                $ids = array_map(fn ($s) => $porSlug[$s] ?? null, $slugs);
                if (count($ids) < 2 || in_array(null, $ids, true)) {
                    return $error('clonar requiere instituciones_clon con al menos dos slugs existentes.');
                }
                $conserva = $porSlug[$d['conserva_original'] ?? ''] ?? null;
                if (! $conserva || ! in_array($conserva, $ids, true)) {
                    return $error('conserva_original debe ser uno de los slugs de instituciones_clon.');
                }
                if ($r['situacion'] !== Sim::MANUAL && array_diff($usos, $ids)) {
                    return $error('instituciones_clon no cubre a todas las instituciones que lo usan ('.implode(', ', $usos).').');
                }
                if ($bloqueo = self::unicoGlobal($r['tabla'], $db)) {
                    return $error("clonar exige R2 anticipado (único compuesto con id_institucion); sigue el único global {$bloqueo}.");
                }
                sort($ids);
                $op['clones'] = $ids;
                $op['conserva'] = $conserva;
                break;
            case 'plantilla':
                if (! in_array($r['tabla'], self::PLANTILLA_PERMITIDA, true)) {
                    return $error('plantilla solo es válida en evento_categorias y escala_notas.');
                }
                break;
            case 'descartar':
                if (! $sinUso || $esCompartido) {
                    return $error('descartar solo es válido para registros sin uso.');
                }
                break;
            case 'archivar_sin_propietario':
                if (! in_array($r['tabla'], array_keys(ArchivoLegado::TABLAS), true)) {
                    return $error('esta tabla no está habilitada para archivo legado.');
                }
                if (! $sinUso || $r['situacion'] !== Sim::MANUAL || ($r['valor_actual'] ?? null) !== null) {
                    return $error('archivar_sin_propietario exige una decisión manual, sin usos y sin institución asignada.');
                }
                if (! $db->table($r['tabla'])->where(Sim::TABLAS[$r['tabla']][0], $r['id'])->whereNull('id_institucion')->exists()) {
                    return $error('el registro operativo ya no existe o tiene institución asignada.');
                }
                break;
            case 'seguir_configuracion':
                if ($r['tabla'] !== 'colegio_imagenes') {
                    return $error('seguir_configuracion solo es válido para imágenes.');
                }
                break;
            default:
                return $error("acción desconocida «{$accion}».");
        }

        return $op;
    }

    /** Nombre del único global (sin id_institucion) que impediría duplicar filas, o null. */
    public static function unicoGlobal(string $tabla, Connection $db): ?string
    {
        foreach (Schema::connection($db->getName())->getIndexes($tabla) as $i) {
            if ($i['unique'] && ! $i['primary'] && ! in_array('id_institucion', $i['columns'], true) && $i['columns'] !== ['id_usuario']) {
                return $i['name'].' ('.implode(', ', $i['columns']).')';
            }
        }

        return null;
    }
}
