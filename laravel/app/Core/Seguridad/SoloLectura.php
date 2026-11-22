<?php

namespace App\Core\Seguridad;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;
use WeakMap;

/**
 * Ejecuta un bloque con la conexión en modo SOLO LECTURA.
 *
 *  - MariaDB/MySQL sin transacción abierta: START TRANSACTION READ ONLY … ROLLBACK
 *    (impuesto por el motor: cualquier INSERT/UPDATE/DELETE/DDL falla).
 *  - MariaDB/MySQL CON una transacción ya abierta (p. ej. RefreshDatabase, o un
 *    llamador dentro de DB::transaction): NO se puede usar START TRANSACTION,
 *    porque confirmaría implícitamente la transacción exterior. Se usa
 *    SAVEPOINT … ROLLBACK TO SAVEPOINT, y además toda sentencia de escritura se
 *    bloquea ANTES de ejecutarse (beforeExecuting).
 *  - SQLite: PRAGMA query_only = ON … OFF (vale también dentro de una transacción).
 *
 * Al terminar siempre se revierte/restaura, aun si el bloque lanza excepción.
 */
class SoloLectura
{
    private const ESCRITURA = '/^\s*(insert|update|delete|replace|alter|create|drop|truncate|rename|grant|revoke|lock|load)\b/i';

    /** Conexiones con el bloqueo previo registrado → nivel de anidamiento activo. */
    private static ?WeakMap $activas = null;

    public static function ejecutar(ConnectionInterface $conexion, Closure $bloque): mixed
    {
        $driver = $conexion->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            if ($conexion->transactionLevel() === 0) {
                $conexion->unprepared('START TRANSACTION READ ONLY');
                try {
                    return $bloque();
                } finally {
                    $conexion->unprepared('ROLLBACK');
                }
            }

            return self::anidado($conexion, $bloque);
        }

        if ($driver === 'sqlite') {
            $conexion->unprepared('PRAGMA query_only = ON');
            try {
                return $bloque();
            } finally {
                $conexion->unprepared('PRAGMA query_only = OFF');
            }
        }

        throw new RuntimeException("Modo solo lectura no soportado para el driver '{$driver}'.");
    }

    private static function anidado(Connection $conexion, Closure $bloque): mixed
    {
        self::$activas ??= new WeakMap;
        if (! isset(self::$activas[$conexion])) {
            self::$activas[$conexion] = 0;
            $conexion->beforeExecuting(function (string $sql) use ($conexion) {
                if ((self::$activas[$conexion] ?? 0) > 0 && preg_match(self::ESCRITURA, $sql)) {
                    throw new RuntimeException('SoloLectura: escritura bloqueada dentro de un bloque de solo lectura: '.mb_substr($sql, 0, 60));
                }
            });
        }

        $punto = 'solo_lectura_'.(self::$activas[$conexion] + 1);
        $conexion->unprepared("SAVEPOINT {$punto}");
        self::$activas[$conexion]++;
        try {
            return $bloque();
        } finally {
            self::$activas[$conexion]--;
            $conexion->unprepared("ROLLBACK TO SAVEPOINT {$punto}");
            $conexion->unprepared("RELEASE SAVEPOINT {$punto}");
        }
    }
}
