<?php

namespace App\Modules\Instituciones\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Transición entre el esquema ANTIGUO (sin id_institucion en catálogos,
 * perfiles, eventos y soportes) y el EXPANDIDO (migraciones B2), decidida
 * por tabla según el esquema REAL de la conexión, nunca por configuración:
 *
 *   legado     → la tabla no tiene id_institucion: ninguna consulta la nombra.
 *   expandido  → la tabla la tiene: se usa para crear, filtrar y validar.
 *
 * Se registra como instancia «scoped» (una por petición/trabajo/prueba), así
 * que un cambio de esquema nunca queda oculto por una caché vieja.
 */
class EsquemaInstitucional
{
    private array $columnas = [];

    private array $unicosGlobales = [];

    private ?int $instituciones = null;

    public static function actual(): self
    {
        return app(self::class);
    }

    public function tieneColumna(string $tabla): bool
    {
        return $this->columnas[$tabla] ??= Schema::hasColumn($tabla, 'id_institucion');
    }

    public function modo(string $tabla): string
    {
        return $this->tieneColumna($tabla) ? 'expandido' : 'legado';
    }

    /** ¿Sigue existiendo un único GLOBAL (sin id_institucion) sobre exactamente estas columnas? */
    public function tieneUnicoGlobal(string $tabla, array $columnas): bool
    {
        return $this->unicosGlobales[$tabla.'|'.implode(',', $columnas)] ??= collect(Schema::getIndexes($tabla))
            ->contains(fn ($i) => $i['unique'] && ! $i['primary'] && $i['columns'] === $columnas);
    }

    public function totalInstituciones(): int
    {
        return $this->instituciones ??= (int) DB::table('instituciones')->count();
    }

    /** Solo para pruebas que cambian el esquema dentro de una misma aplicación. */
    public function olvidar(): void
    {
        $this->columnas = [];
        $this->unicosGlobales = [];
        $this->instituciones = null;
    }
}
