<?php

namespace App\Core\Seguridad;

use Illuminate\Database\Connection;
use RuntimeException;
use Throwable;

/**
 * Impide aplicar por accidente las migraciones de la Etapa 2 (M1–M4) sobre una
 * base no autorizada — en particular `colegio` con un simple `php artisan migrate`.
 *
 * Se permite si:
 *  - la conexión efectiva es la base de pruebas autorizada (GuardiaBaseDePruebas), o
 *  - la variable de entorno del PROCESO ETAPA2_MIGRACIONES_AUTORIZADAS es
 *    exactamente el nombre de la base efectiva (SELECT DATABASE()). Debe pasarse
 *    explícitamente al ejecutar (no en .env), p. ej.:
 *      ETAPA2_MIGRACIONES_AUTORIZADAS=colegio php artisan migrate
 *    y solo con autorización escrita (bloque B10) y respaldo verificado.
 *
 * --pretend no ejecuta DDL, así que se permite (sirve para revisar el SQL).
 * Cualquier error al verificar cuenta como no autorizado (falla cerrado).
 */
class GuardiaMigracionesEtapa2
{
    public const VARIABLE = 'ETAPA2_MIGRACIONES_AUTORIZADAS';

    public static function asegurar(Connection $conexion, string $migracion): void
    {
        if ($conexion->pretending() || GuardiaBaseDePruebas::esAutorizada($conexion)) {
            return;
        }

        try {
            $efectiva = $conexion->getDriverName() === 'sqlite' ? null : (string) $conexion->selectOne('SELECT DATABASE() AS b')->b;
        } catch (Throwable) {
            $efectiva = null;
        }
        $autorizada = getenv(self::VARIABLE);

        if ($efectiva === null || $autorizada === false || $autorizada === '' || $autorizada !== $efectiva) {
            throw new RuntimeException("BLOQUEADO: {$migracion} (Etapa 2) no está autorizada sobre la base «".($efectiva ?? '?').'». '
                .'Solo se aplica en la base de pruebas o con '.self::VARIABLE.'=<nombre exacto de la base> en el entorno del proceso, con autorización escrita y respaldo verificado. No se ejecutó ningún cambio.');
        }
    }
}
