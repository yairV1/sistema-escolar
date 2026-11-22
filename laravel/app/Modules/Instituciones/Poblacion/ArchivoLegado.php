<?php

namespace App\Modules\Instituciones\Poblacion;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ArchivoLegado
{
    public const TABLAS = [
        'colegio_configuracion' => 'id_configuracion',
        'colegio_imagenes' => 'id_imagen',
        'landing_contenido' => 'id_contenido',
        'landing_galeria' => 'id_foto',
        'landing_noticias' => 'id_noticia',
    ];

    /** Columnas que guardan rutas de archivos en el disco público (por tabla). */
    public const COLUMNAS_ARCHIVO = [
        'colegio_configuracion' => ['logo', 'imagen_hero'],
        'colegio_imagenes' => ['imagen'],
        'landing_galeria' => ['imagen'],
        'landing_noticias' => ['imagen'],
        'landing_contenido' => [],   // la ruta está en «valor» cuando clave = hero_imagen
    ];

    public function __construct(private Connection $db) {}

    /**
     * Archivos públicos que referencia una fila: ruta, si existe, tamaño y SHA-256.
     * Solo lectura: nunca mueve ni borra archivos.
     */
    public static function manifiesto(string $tabla, array $fila): array
    {
        $rutas = [];
        foreach (self::COLUMNAS_ARCHIVO[$tabla] ?? [] as $col) {
            if (! empty($fila[$col])) {
                $rutas[] = (string) $fila[$col];
            }
        }
        if ($tabla === 'landing_contenido' && ($fila['clave'] ?? null) === 'hero_imagen' && ! empty($fila['valor'])) {
            $rutas[] = (string) $fila['valor'];
        }
        $disco = Storage::disk('public');

        return array_map(function (string $ruta) use ($disco) {
            $existe = $disco->exists($ruta);

            return [
                'ruta' => $ruta,
                'existe' => $existe,
                'bytes' => $existe ? $disco->size($ruta) : null,
                'sha256' => $existe ? hash_file('sha256', $disco->path($ruta)) : null,
            ];
        }, $rutas);
    }

    /** Compara el manifiesto de un lote con el disco actual. Devuelve las diferencias (vacío = intacto). */
    public function verificarArchivos(string $lote): array
    {
        $diferencias = [];
        foreach ($this->db->table('migracion_legado_archivos')->where('lote', $lote)->orderBy('id')->get() as $a) {
            foreach (json_decode((string) ($a->archivos ?? '[]'), true) ?: [] as $antes) {
                $ahora = self::manifiesto('colegio_imagenes', ['imagen' => $antes['ruta']])[0];
                if ($ahora['existe'] !== $antes['existe'] || $ahora['sha256'] !== $antes['sha256']) {
                    $diferencias[] = ['tabla' => $a->tabla_origen, 'id' => (int) $a->id_origen, 'ruta' => $antes['ruta'], 'antes' => $antes, 'ahora' => $ahora];
                }
            }
        }

        return $diferencias;
    }

    public function archivar(string $lote, string $tabla, int $id): array
    {
        $pk = self::TABLAS[$tabla] ?? throw new RuntimeException("{$tabla} no está habilitada para archivo legado.");
        $fila = $this->db->table($tabla)->where($pk, $id)->first();
        if (! $fila || $fila->id_institucion !== null) {
            throw new RuntimeException("{$tabla}#{$id} ya no existe o tiene institución asignada; no se archiva.");
        }

        $payload = json_encode((array) $fila, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $checksum = hash('sha256', $payload);
        $idArchivo = (int) $this->db->table('migracion_legado_archivos')->insertGetId([
            'lote' => $lote,
            'tabla_origen' => $tabla,
            'id_origen' => $id,
            'payload' => $payload,
            'checksum_sha256' => $checksum,
            'archivado_at' => now(),
        ] + (Schema::connection($this->db->getName())->hasColumn('migracion_legado_archivos', 'archivos')
            ? ['archivos' => json_encode(self::manifiesto($tabla, (array) $fila), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
            : []));

        $archivo = $this->db->table('migracion_legado_archivos')->where('id', $idArchivo)->first();
        if (! $archivo || ! hash_equals($checksum, hash('sha256', $archivo->payload))) {
            throw new RuntimeException("{$tabla}#{$id}: la verificación del archivo legado falló.");
        }

        $eliminadas = $this->db->table($tabla)->where($pk, $id)->whereNull('id_institucion')->delete();
        if ($eliminadas !== 1) {
            throw new RuntimeException("{$tabla}#{$id}: cambió durante el archivo; se revierte la operación.");
        }

        return ['id' => $idArchivo, 'checksum' => $checksum];
    }

    public function restaurar(int $idArchivo, string $tabla, int $id, string $checksum): void
    {
        $pk = self::TABLAS[$tabla] ?? throw new RuntimeException("{$tabla} no está habilitada para restauración.");
        $archivo = $this->db->table('migracion_legado_archivos')->where('id', $idArchivo)->first();
        if (! $archivo
            || $archivo->tabla_origen !== $tabla
            || (int) $archivo->id_origen !== $id
            || ! hash_equals($checksum, $archivo->checksum_sha256)
            || ! hash_equals($checksum, hash('sha256', $archivo->payload))) {
            throw new RuntimeException("No se revierte: el archivo de {$tabla}#{$id} falta o no coincide con su checksum.");
        }
        if ($archivo->restaurado_at !== null) {
            throw new RuntimeException("No se revierte: {$tabla}#{$id} ya fue restaurado.");
        }

        $fila = json_decode($archivo->payload, true, 512, JSON_THROW_ON_ERROR);
        if (($fila[$pk] ?? null) === null || (int) $fila[$pk] !== $id || ($fila['id_institucion'] ?? null) !== null) {
            throw new RuntimeException("No se revierte: el payload de {$tabla}#{$id} no conserva el ID y propietario esperados.");
        }
        if ($this->db->table($tabla)->where($pk, $id)->exists()) {
            throw new RuntimeException("No se revierte: {$tabla}#{$id} ya existe en la tabla operativa.");
        }
        if (! collect(Schema::connection($this->db->getName())->getColumns($tabla))
            ->firstWhere('name', 'id_institucion')['nullable']) {
            throw new RuntimeException("No se revierte: primero debe revertirse B8 para restaurar {$tabla}#{$id} sin propietario.");
        }

        $this->db->table($tabla)->insert($fila);
        $actualizado = $this->db->table('migracion_legado_archivos')
            ->where('id', $idArchivo)
            ->whereNull('restaurado_at')
            ->update(['restaurado_at' => now()]);
        if ($actualizado !== 1) {
            throw new RuntimeException("No se revierte: no se pudo marcar la restauración de {$tabla}#{$id}.");
        }
    }
}
