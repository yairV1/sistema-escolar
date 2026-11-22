<?php

namespace App\Modules\Instituciones\Console\Commands;

use App\Core\Seguridad\SoloLectura;
use App\Modules\Instituciones\Poblacion\AplicadorPoblacion;
use App\Modules\Instituciones\Poblacion\Correspondencia;
use App\Modules\Instituciones\Poblacion\SimuladorPoblacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * php artisan institucion:poblar
 *
 *  --simular                     B3. Plan (solo lectura) + plantilla de correspondencia v3 con grupos.
 *  --validar=<archivo>           Revisa un archivo completado: decisión de cada registro (individual o
 *                                por grupo) y errores. Solo lectura.
 *  --aplicar=<archivo>           B4. SOLO en colegio_tests (conexión efectiva + usuario). Ver AplicadorPoblacion.
 *  --revertir=<lote>             Deshace un lote aplicado (misma restricción de base).
 *
 * Los archivos se leen y escriben en storage/app/auditoria/ (ignorado por git).
 */
class PoblarPertenenciaCommand extends Command
{
    protected $signature = 'institucion:poblar
        {--simular : Calcula el plan sin modificar la base}
        {--validar= : Valida storage/app/auditoria/<archivo>.json sin aplicar}
        {--aplicar= : Aplica storage/app/auditoria/<archivo>.json (solo colegio_tests)}
        {--revertir= : Revierte el lote indicado (solo colegio_tests)}
        {--json= : Con --simular: guarda el plan en storage/app/auditoria/<nombre>.json}
        {--correspondencia= : Con --simular: genera storage/app/auditoria/<nombre>.json con las decisiones en null}
        {--limite=15 : Filas a mostrar por sección en consola}';

    protected $description = 'Pertenencia institucional: simular (B3) y, solo en la base de pruebas, aplicar/revertir (B4)';

    public function handle(): int
    {
        $modos = array_filter(['simular' => $this->option('simular'), 'validar' => $this->option('validar'), 'aplicar' => $this->option('aplicar'), 'revertir' => $this->option('revertir')]);
        if (count($modos) !== 1) {
            $this->error('Indique exactamente un modo: --simular | --validar=<archivo> | --aplicar=<archivo> | --revertir=<lote>.');

            return self::FAILURE;
        }
        $conexion = DB::connection();
        $modo = array_key_first($modos);

        try {
            return match ($modo) {
                'simular' => $this->simular($conexion),
                'validar' => $this->validar($conexion),
                'aplicar' => $this->aplicar($conexion),
                'revertir' => $this->revertir($conexion),
            };
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    // ---------------------------------------------------------------- simular
    private function simular($conexion): int
    {
        $this->line("Base: <info>{$conexion->getDatabaseName()}</info> ({$conexion->getDriverName()}) — SIMULACIÓN, modo SOLO LECTURA");
        $plan = SoloLectura::ejecutar($conexion, fn () => (new SimuladorPoblacion($conexion))->ejecutar());
        $this->mostrar($plan);

        if ($nombre = $this->option('json')) {
            $this->info('Plan JSON: '.$this->guardar($nombre, ['formato' => 'plan-poblacion/v1', 'base' => $conexion->getDatabaseName()] + $plan));
        }
        if ($nombre = $this->option('correspondencia')) {
            $archivo = Correspondencia::generar($plan, $conexion->getDatabaseName());
            $this->mostrarGrupos($archivo);
            $this->info('Archivo de correspondencia: '.$this->guardar($nombre, $archivo));
        }
        $this->line("Huella del plan: <comment>{$plan['huella']}</comment>");

        return self::SUCCESS;
    }

    // ---------------------------------------------------------------- validar
    private function validar($conexion): int
    {
        $archivo = $this->leer($this->option('validar'));
        $plan = SoloLectura::ejecutar($conexion, fn () => (new SimuladorPoblacion($conexion))->ejecutar());
        [$ops, $errores] = SoloLectura::ejecutar($conexion, fn () => Correspondencia::resolver($archivo, $plan, $conexion));
        $this->mostrarGrupos($archivo);

        if ($errores) {
            $this->error(count($errores).' error(es): el archivo NO se puede aplicar.');
            foreach (array_slice($errores, 0, (int) $this->option('limite') * 4) as $e) {
                $this->line("  · {$e}");
            }

            return self::FAILURE;
        }
        $this->info('Archivo válido. Operaciones que se aplicarían:');
        $this->table(['Tabla', 'ID', 'Acción', 'Destino / copias', 'Origen'], array_map(fn ($o) => [
            $o['tabla'], $o['id'], $o['accion'], $o['destino'] ?? ($o['clones'] ? implode(', ', $o['clones']).' (original: '.$o['conserva'].')' : '—'), $o['regla'],
        ], array_values($ops)));

        return self::SUCCESS;
    }

    // ---------------------------------------------------------------- aplicar
    private function aplicar($conexion): int
    {
        AplicadorPoblacion::asegurarBaseAutorizada($conexion);   // antes de leer nada
        $archivo = $this->leer($this->option('aplicar'));
        $r = (new AplicadorPoblacion($conexion))->aplicar($archivo);

        if ($r['estado'] === 'rechazado') {
            $this->error(count($r['errores']).' error(es): no se aplicó NADA.');
            foreach ($r['errores'] as $e) {
                $this->line("  · {$e}");
            }

            return self::FAILURE;
        }
        if ($r['estado'] === 'ya_aplicado') {
            $this->info("Este plan ya fue aplicado (lote {$r['lote']}). Sin cambios.");

            return self::SUCCESS;
        }
        $this->info("Aplicado en {$conexion->getDatabaseName()}. Lote: {$r['lote']}");
        $this->table(['Acción', 'Registros'], collect($r['operaciones'])->map(fn ($n, $a) => [$a, $n])->values()->all());

        return self::SUCCESS;
    }

    private function revertir($conexion): int
    {
        $r = (new AplicadorPoblacion($conexion))->revertir((string) $this->option('revertir'));
        $this->info($r['estado'] === 'revertido' ? "Lote {$r['lote']} revertido ({$r['filas']} registros de trazabilidad)." : "Nada que revertir para {$r['lote']}.");

        return self::SUCCESS;
    }

    // ----------------------------------------------------------------- salida
    private function mostrar(array $plan): void
    {
        $e = $plan['esquema'];
        $this->line('Columnas B2: '.($e['b2_aplicada'] ? '<info>presentes en las 16 tablas</info>' : '<comment>'.count($e['tablas_con_id_institucion']).'/16 tablas</comment> (la simulación no las necesita)'));
        $this->table(['ID', 'Slug', 'Institución', 'Estado'], array_map(fn ($i) => array_values($i), $plan['instituciones']));

        $this->line('<options=bold>Resumen por tabla</>');
        $this->table(['Tabla', 'Total', 'Asignar', 'Ya asig.', 'Plantilla', 'Compartido', 'Copias', 'Sin dueño', 'Manual', 'Huérfano', 'Inconsist.'],
            collect($plan['resumen_por_tabla'])->map(fn ($r, $t) => [$t, $r['total'], $r['inequivoca'], $r['ya_asignado'], $r['plantilla'], $r['compartido'], $r['copias'], $r['sin_uso_sin_propietario'], $r['decision_manual'], $r['huerfano'], $r['relacion_inconsistente']])->values()->all());

        $t = $plan['totales'];
        $this->line("Asignables automáticamente: <info>{$t['asignables_automaticamente']}</info> · a clonar: <comment>{$t['a_clonar']}</comment> ({$t['copias_necesarias']} copias) · decisiones manuales: <comment>{$t['decisiones_manuales']}</comment> · huérfanos: {$t['huerfanos']} · inconsistentes: {$t['inconsistentes']} · plantillas: {$t['plantillas_sin_cambio']}");

        $limite = max(1, (int) $this->option('limite'));
        if ($plan['clonaciones']) {
            $this->newLine();
            $this->line('<options=bold>Clonaciones simuladas</> (no se crea nada)');
            $this->table(['Tabla', 'ID', 'Registro', 'Instituciones', 'Copias', 'Refs. a repuntar', 'Riesgos'], array_map(fn ($c) => [
                $c['tabla'], $c['id'], $c['descripcion'], implode(', ', $c['instituciones']), $c['copias_necesarias'],
                $c['referencias_a_repuntar_minimo'].'–'.$c['referencias_a_repuntar_maximo'], count($c['riesgos_integridad']),
            ], array_slice($plan['clonaciones'], 0, $limite)));
        }

        $pendientes = array_values(array_filter($plan['registros'], fn ($r) => ! in_array($r['situacion'], ['inequivoca', 'ya_asignado', 'plantilla'], true)));
        $this->newLine();
        $this->line('<options=bold>Requieren decisión o revisión</> — '.count($pendientes));
        if ($pendientes) {
            $this->table(['Tabla', 'ID', 'Registro', 'Situación', 'Candidatas', 'Propuesta'], array_map(fn ($r) => [
                $r['tabla'], $r['id'], mb_strimwidth((string) $r['descripcion'], 0, 30, '…'), $r['situacion'], implode(', ', $r['instituciones']) ?: '—', $r['accion_propuesta'],
            ], array_slice($pendientes, 0, $limite)));
            if (count($pendientes) > $limite) {
                $this->line('  … '.(count($pendientes) - $limite).' más (use --json)');
            }
        }

        $this->line('Relaciones cruzadas: '.count($plan['relaciones_cruzadas']).' · referencias huérfanas: '.count($plan['referencias_huerfanas']).' · únicos globales que bloquean clonar: '.count($plan['restricciones_unicas']).' · dependencias de ColegioConfiguracion::singleton(): '.collect($plan['dependencias_singleton'])->countBy('tipo')->map(fn ($n, $t) => "$n $t".'s')->implode(', '));
    }

    private function mostrarGrupos(array $archivo): void
    {
        $individuales = collect($archivo['decisiones'] ?? [])->filter(fn ($d) => ($d['decision'] ?? null) !== null)->groupBy('grupo');
        foreach ($archivo['grupos'] ?? [] as $g) {
            $this->newLine();
            $this->line("<options=bold>Grupo {$g['grupo']}</> — {$g['descripcion']} ({$g['tabla']}, ".count($g['registros']).' registros) · decisión del grupo: <comment>'.($g['decision'] ?? 'null').'</comment> · opciones: '.implode(', ', $g['opciones'] ?? []));
            $this->line('  '.collect($g['registros'])->map(fn ($r) => "#{$r['id']} ".mb_strimwidth((string) $r['descripcion'], 0, 28, '…'))->implode(' · '));
            $propias = $individuales[$g['grupo']] ?? collect();
            if ($propias->isNotEmpty()) {
                $this->line('  Con decisión individual (prevalece sobre la del grupo): '.$propias->map(fn ($d) => "#{$d['id']}={$d['decision']}")->implode(', '));
            }
        }
    }

    private function leer(string $nombre): array
    {
        $ruta = storage_path('app/auditoria/'.preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($nombre, '.json')).'.json');
        if (! is_file($ruta)) {
            throw new \RuntimeException("No existe {$ruta}.");
        }
        $datos = json_decode((string) file_get_contents($ruta), true);
        if (! is_array($datos)) {
            throw new \RuntimeException("{$ruta} no es JSON válido.");
        }

        return $datos;
    }

    private function guardar(string $nombre, array $datos): string
    {
        $dir = storage_path('app/auditoria');
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $ruta = $dir.'/'.preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($nombre, '.json')).'.json';
        file_put_contents($ruta, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $ruta;
    }
}
