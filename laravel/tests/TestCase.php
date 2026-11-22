<?php

namespace Tests;

use App\Core\Seguridad\GuardiaBaseDePruebas;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Antes de que RefreshDatabase / DatabaseMigrations / DatabaseTruncation
     * toquen la base, se verifica la conexión EFECTIVA: SQLite en memoria o
     * MariaDB `colegio_tests` con su usuario limitado. Cualquier otra conexión
     * aborta la prueba sin ejecutar migrate:fresh.
     */
    protected function setUpTraits()
    {
        $usos = class_uses_recursive(static::class);

        if (array_intersect([RefreshDatabase::class, DatabaseMigrations::class, DatabaseTruncation::class], $usos)) {
            GuardiaBaseDePruebas::asegurar(DB::connection(), static::class.' (reinicio de base de datos)');
        }

        return parent::setUpTraits();
    }
}
