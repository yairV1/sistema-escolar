<?php

namespace Tests\Feature;

use App\Core\Seguridad\GuardiaBaseDePruebas;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\TestCase;

/**
 * La protección contra operaciones destructivas fuera de la base de pruebas.
 * Ninguna de estas pruebas ejecuta comandos destructivos: solo consulta la
 * conexión efectiva (SELECT / PRAGMA).
 */
class GuardiaBaseDePruebasTest extends TestCase
{
    public function test_la_conexion_de_la_suite_esta_autorizada(): void
    {
        $this->assertNull(GuardiaBaseDePruebas::motivoDeRechazo(DB::connection()));
        $this->assertFalse((new \ReflectionProperty(FreshCommand::class, 'prohibitedFromRunning'))->getValue(), 'migrate:fresh debe estar permitido en la base de pruebas');
    }

    public function test_rechaza_sqlite_en_archivo(): void
    {
        $archivo = tempnam(sys_get_temp_dir(), 'guardia_').'.sqlite';
        touch($archivo);
        config(['database.connections.sqlite_archivo' => ['driver' => 'sqlite', 'database' => $archivo, 'prefix' => '']]);

        try {
            $motivo = GuardiaBaseDePruebas::motivoDeRechazo(DB::connection('sqlite_archivo'));
            $this->assertNotNull($motivo);
            $this->assertStringContainsString('no está en memoria', $motivo);
            $this->expectException(RuntimeException::class);
            GuardiaBaseDePruebas::asegurar(DB::connection('sqlite_archivo'), 'prueba');
        } finally {
            DB::purge('sqlite_archivo');
            @unlink($archivo);
        }
    }

    public function test_falla_cerrado_si_no_puede_conectar(): void
    {
        config(['database.connections.inexistente' => ['driver' => 'mariadb', 'host' => '127.0.0.1', 'port' => 1, 'database' => 'x', 'username' => 'x', 'password' => 'x']]);

        $this->assertStringContainsString('No se pudo verificar', GuardiaBaseDePruebas::motivoDeRechazo(DB::connection('inexistente')));
    }

    #[Group('mariadb')]
    public function test_rechaza_otra_base_aunque_use_el_usuario_de_pruebas(): void
    {
        $base = config('database.connections.mariadb');
        config(['database.connections.otra_base' => ['database' => 'colegio'] + $base]);

        $motivo = GuardiaBaseDePruebas::motivoDeRechazo(DB::connection('otra_base'));

        $this->assertNotNull($motivo, 'una conexión a la base real nunca puede estar autorizada');
        DB::purge('otra_base');
    }
}
