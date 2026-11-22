<?php

namespace App\Core\Seguridad;

use Illuminate\Database\ConnectionInterface;
use RuntimeException;
use Throwable;

/**
 * Impide operaciones destructivas (RefreshDatabase, migrate:fresh, db:wipe…)
 * fuera de la base de pruebas autorizada.
 *
 * Verifica la CONEXIÓN EFECTIVA, no las variables de entorno:
 *  - SQLite:  la base principal debe ser realmente en memoria
 *             (PRAGMA database_list sin archivo).
 *  - MariaDB: SELECT DATABASE() debe ser la base autorizada Y
 *             CURRENT_USER() su usuario limitado (nunca root ni el de la app).
 * Cualquier error al comprobarlo cuenta como NO autorizado (falla cerrado).
 */
class GuardiaBaseDePruebas
{
    public const BASE_AUTORIZADA = 'colegio_tests';

    public const USUARIO_AUTORIZADO = 'colegio_tests';

    /** Motivo por el que la conexión NO está autorizada, o null si lo está. */
    public static function motivoDeRechazo(ConnectionInterface $conexion): ?string
    {
        try {
            $driver = $conexion->getDriverName();

            if ($driver === 'sqlite') {
                $principal = collect($conexion->select('PRAGMA database_list'))
                    ->first(fn ($fila) => ($fila->name ?? null) === 'main');

                return $principal !== null && ($principal->file ?? '') === ''
                    ? null
                    : 'SQLite no está en memoria (archivo: '.($principal->file ?? '?').').';
            }

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $fila = $conexion->selectOne('SELECT DATABASE() AS base, CURRENT_USER() AS usuario');
                $base = (string) ($fila->base ?? '');
                $usuario = strtolower(strstr((string) ($fila->usuario ?? ''), '@', true) ?: '');

                if ($base !== self::BASE_AUTORIZADA) {
                    return "Base efectiva '{$base}' distinta de '".self::BASE_AUTORIZADA."'.";
                }
                if ($usuario !== self::USUARIO_AUTORIZADO) {
                    return "Usuario efectivo '{$usuario}' distinto de '".self::USUARIO_AUTORIZADO."'.";
                }

                return null;
            }

            return "Driver '{$driver}' no autorizado para operaciones destructivas.";
        } catch (Throwable $e) {
            return 'No se pudo verificar la conexión: '.$e->getMessage();
        }
    }

    public static function esAutorizada(ConnectionInterface $conexion): bool
    {
        return static::motivoDeRechazo($conexion) === null;
    }

    public static function asegurar(ConnectionInterface $conexion, string $operacion): void
    {
        if ($motivo = static::motivoDeRechazo($conexion)) {
            throw new RuntimeException("BLOQUEADO: {$operacion} solo puede ejecutarse sobre la base de pruebas autorizada. {$motivo}");
        }
    }
}
