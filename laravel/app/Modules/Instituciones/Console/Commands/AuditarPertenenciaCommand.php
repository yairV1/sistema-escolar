<?php

namespace App\Modules\Instituciones\Console\Commands;

use App\Core\Seguridad\SoloLectura;
use App\Modules\Instituciones\Auditoria\AuditoriaPertenencia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan institucion:auditar
 *
 * Informe de pertenencia institucional (Etapa 2, B1). EXCLUSIVAMENTE LECTURA:
 * la auditoría corre dentro de SoloLectura (transacción READ ONLY en MariaDB,
 * PRAGMA query_only en SQLite). No asigna instituciones ni modifica registros.
 * Los únicos archivos que escribe son los informes pedidos, en storage/app/auditoria/.
 */
class AuditarPertenenciaCommand extends Command
{
    protected $signature = 'institucion:auditar
        {--json= : Guarda el informe completo en storage/app/auditoria/<nombre>.json}
        {--plantilla-correspondencia= : Genera storage/app/auditoria/<nombre>.json con las decisiones pendientes (todas en null)}
        {--limite=20 : Filas a mostrar por sección en consola}
        {--estricto : Termina con código 1 si hay hallazgos críticos}';

    protected $description = 'Audita la pertenencia institucional de los datos (solo lectura)';

    public function handle(): int
    {
        $conexion = DB::connection();
        $this->line("Base auditada: <info>{$conexion->getDatabaseName()}</info> ({$conexion->getDriverName()}) — modo SOLO LECTURA");

        $informe = SoloLectura::ejecutar($conexion, fn () => (new AuditoriaPertenencia($conexion))->ejecutar());

        $this->mostrar($informe);

        if ($nombre = $this->option('json')) {
            $this->info('Informe JSON: '.$this->guardar($nombre, $informe + ['generado' => now()->toIso8601String(), 'base' => $conexion->getDatabaseName()]));
        }
        if ($nombre = $this->option('plantilla-correspondencia')) {
            $this->info('Plantilla de correspondencia: '.$this->guardar($nombre, $this->plantilla($informe, $conexion->getDatabaseName())));
        }

        $criticos = count(array_filter($informe['hallazgos'], fn ($h) => $h['severidad'] === AuditoriaPertenencia::CRITICO));

        return $this->option('estricto') && $criticos > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function mostrar(array $informe): void
    {
        $this->newLine();
        $this->table(['ID', 'Slug', 'Institución', 'Estado'], array_map(fn ($i) => array_values($i), $informe['instituciones']));

        $titulos = [
            'sin_institucion' => 'Registros sin institución',
            'relaciones_cruzadas' => 'Relaciones entre colegios diferentes',
            'catalogos' => 'Catálogos compartidos o ambiguos',
            'asignaciones_inconsistentes' => 'Asignaciones inconsistentes',
            'referencias_huerfanas' => 'Referencias huérfanas',
            'configuracion' => 'Configuración institucional',
            'restricciones_unicas' => 'Restricciones únicas incompatibles con varios colegios',
            'coherencia_institucional' => 'Coherencia institucional por columnas (B2)',
        ];
        $limite = max(1, (int) $this->option('limite'));

        foreach ($titulos as $seccion => $titulo) {
            $filas = array_values(array_filter($informe['hallazgos'], fn ($h) => $h['seccion'] === $seccion));
            $this->newLine();
            $this->line("<options=bold>{$titulo}</> — ".count($filas).' hallazgo(s)');
            if ($filas) {
                $this->table(['Severidad', 'Tabla', 'ID', 'Detalle'], array_map(
                    fn ($h) => [$h['severidad'], $h['tabla'], $h['id'] ?? '—', $h['detalle']],
                    array_slice($filas, 0, $limite)
                ));
                if (count($filas) > $limite) {
                    $this->line('  … '.(count($filas) - $limite).' más (use --json para el informe completo)');
                }
            }
        }

        $this->newLine();
        $this->line('<options=bold>Clasificación de catálogos</>');
        $this->table(['Clase', 'Filas'], collect($informe['resumen']['catalogos_por_clase'] ?? [])->map(fn ($n, $c) => [$c, $n])->values()->all());
        $this->line('  resoluble: usado por una sola institución · compartido: varias (clonar) · plantilla: global de solo lectura · asignado: ya tiene institución coherente');
        $this->line('  sin_uso_institucion_unica: sin uso, hay una sola institución · sin_uso_ambiguo: requiere decisión manual');
    }

    /** Decisiones pendientes: SIEMPRE en null. El comando no decide nada. */
    private function plantilla(array $informe, string $base): array
    {
        $decisiones = [];
        foreach ($informe['catalogos'] as $c) {
            if (in_array($c['clase'], ['compartido', 'sin_uso_ambiguo'], true)) {
                $decisiones[] = ['tabla' => $c['tabla'], 'id' => $c['id'], 'descripcion' => $c['nombre'], 'motivo' => $c['clase'],
                    'instituciones_detectadas' => $c['instituciones'], 'decision' => null, 'institucion_destino' => null, 'instituciones_clon' => []];
            }
        }
        foreach ($informe['hallazgos'] as $h) {
            if (in_array($h['seccion'], ['sin_institucion', 'configuracion'], true) && $h['severidad'] === AuditoriaPertenencia::CRITICO) {
                $decisiones[] = ['tabla' => $h['tabla'], 'id' => $h['id'], 'descripcion' => $h['detalle'], 'motivo' => $h['seccion'],
                    'instituciones_detectadas' => [], 'decision' => null, 'institucion_destino' => null, 'instituciones_clon' => []];
            }
        }

        return [
            'formato' => 'correspondencia-institucional/v1',
            'generado' => now()->toIso8601String(),
            'base' => $base,
            'instrucciones' => 'Completar "decision" en cada entrada: asignar (usar institucion_destino = slug) | clonar (instituciones_clon = [slugs]) | plantilla (global de solo lectura) | descartar (no migrar). Ninguna entrada se aplica mientras "decision" sea null. Ver docs/seguridad/etapa2-b0-b1.md.',
            'instituciones' => array_map(fn ($i) => ['id' => $i['id_institucion'], 'slug' => $i['slug'], 'nombre' => $i['nombre']], $informe['instituciones']),
            'decisiones' => $decisiones,
        ];
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
