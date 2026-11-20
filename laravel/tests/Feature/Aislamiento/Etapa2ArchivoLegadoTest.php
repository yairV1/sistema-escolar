<?php

namespace Tests\Feature\Aislamiento;

use App\Core\Seguridad\SoloLectura;
use App\Modules\Instituciones\Poblacion\AplicadorPoblacion;
use App\Modules\Instituciones\Poblacion\ArchivoLegado;
use App\Modules\Instituciones\Poblacion\Correspondencia;
use App\Modules\Instituciones\Poblacion\SimuladorPoblacion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Tests\Support\EscenarioDosColegios;
use Tests\TestCase;

class Etapa2ArchivoLegadoTest extends TestCase
{
    private EscenarioDosColegios $escenario;

    private function migracionArchivo(): Migration
    {
        return require database_path('migrations/etapa2/b10/2026_11_20_100000_etapa2_b10_archivo_legado.php');
    }

    /**
     * Esta clase aplica DDL (migraciones de B10 y B8), así que no puede usar la
     * transacción de RefreshDatabase (en MariaDB el DDL la confirmaría). Para ser
     * aislada e independiente del orden: verifica la base de pruebas, parte de una
     * base recién migrada y, al terminar (también si la prueba falla), la deja
     * recién migrada y SIN datos, que es el estado que esperan las demás clases.
     */
    private function baseLimpia(string $momento): void
    {
        \App\Core\Seguridad\GuardiaBaseDePruebas::asegurar(DB::connection(), "Etapa2ArchivoLegadoTest ({$momento})");
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    protected function tearDown(): void
    {
        $this->baseLimpia('limpieza final');
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseLimpia('preparación');
        $this->escenario = EscenarioDosColegios::crear();
        $this->migracionArchivo()->up();
        if (! DB::table('colegio_configuracion')->exists()) {
            DB::table('colegio_configuracion')->insert(['nombre_colegio' => 'Legado de prueba', 'created_at' => now(), 'updated_at' => now()]);
        }
        $idConfiguracion = DB::table('colegio_configuracion')->value('id_configuracion');
        DB::table('colegio_imagenes')->insert([
            'id_configuracion' => $idConfiguracion, 'imagen' => 'legado/imagen-prueba.jpg', 'tipo' => 'fachada',
            'orden' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('landing_galeria')->insert([
            'imagen' => 'legado/galeria-prueba.jpg', 'orden' => 1, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('landing_noticias')->insert([
            'titulo' => 'Noticia de prueba', 'descripcion' => 'Contenido ficticio', 'fecha' => '2026-11-01',
            'orden' => 1, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function archivoAprobado(): array
    {
        $plan = SoloLectura::ejecutar(DB::connection(), fn () => (new SimuladorPoblacion(DB::connection()))->ejecutar());
        $archivo = Correspondencia::generar($plan, DB::connection()->getDatabaseName());
        foreach ($archivo['decisiones'] as &$decision) {
            if ($decision['situacion'] === SimuladorPoblacion::COMPARTIDO) {
                $decision['decision'] = 'clonar';
                $decision['instituciones_clon'] = $decision['instituciones_candidatas'];
                $decision['conserva_original'] = 'colegio-alfa';
            }
        }
        unset($decision);
        foreach ($archivo['grupos'] as &$grupo) {
            $grupo['decision'] = $grupo['grupo'] === 'categorias_calendario' ? 'plantilla' : 'archivar_sin_propietario';
        }
        unset($grupo);
        $archivo['aprobacion'] = [
            'ambito' => 'pruebas',
            'base' => DB::connection()->getDatabaseName(),
            'huella_plan' => $plan['huella'],
            'aprobado_por' => 'Suite aislada B10',
            'fecha' => '2026-11-20T12:00:00-05:00',
        ];

        return $archivo;
    }

    private function aplicador(): AplicadorPoblacion
    {
        return new AplicadorPoblacion(DB::connection());
    }

    private function migracionB8(): Migration
    {
        return require database_path('migrations/etapa2/b8/2026_11_19_120000_etapa2_b8_restricciones_institucionales.php');
    }

    private function huellasDeTablas(): array
    {
        $tablas = collect(Schema::getTables())->pluck('name')->sort()->values();

        return $tablas->mapWithKeys(function ($tabla) {
            $filas = DB::table($tabla)->get()->map(fn ($fila) => json_encode((array) $fila))->all();
            sort($filas);

            return [$tabla => $filas];
        })->all();
    }

    /** B10 (preparación): manifiesto de archivos referenciados; archivar y restaurar no tocan el disco. */
    public function test_manifiesto_de_archivos_y_disco_intacto(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disco = \Illuminate\Support\Facades\Storage::disk('public');
        $disco->put('legado/imagen-prueba.jpg', 'contenido-imagen');
        $disco->put('legado/galeria-prueba.jpg', 'contenido-galeria');
        DB::table('colegio_configuracion')->whereNull('id_institucion')->update(['logo' => 'legado/logo-inexistente.png']);
        $antesDisco = collect($disco->allFiles())->mapWithKeys(fn ($f) => [$f => hash('sha256', $disco->get($f))])->all();

        $r = $this->aplicador()->aplicar($this->archivoAprobado());
        $this->assertSame('aplicado', $r['estado'], implode("\n", $r['errores']));

        $imagen = DB::table('migracion_legado_archivos')->where('tabla_origen', 'colegio_imagenes')->first();
        $manifiesto = json_decode($imagen->archivos, true);
        $this->assertSame('legado/imagen-prueba.jpg', $manifiesto[0]['ruta']);
        $this->assertTrue($manifiesto[0]['existe']);
        $this->assertSame(hash('sha256', 'contenido-imagen'), $manifiesto[0]['sha256']);
        $logo = collect(json_decode(DB::table('migracion_legado_archivos')->where('tabla_origen', 'colegio_configuracion')->value('archivos'), true))->firstWhere('ruta', 'legado/logo-inexistente.png');
        $this->assertFalse($logo['existe'], 'una referencia rota se registra como tal, no se inventa');

        $legado = new ArchivoLegado(DB::connection());
        $this->assertSame([], $legado->verificarArchivos($r['lote']), 'el disco sigue igual tras archivar');
        $disco->put('legado/galeria-prueba.jpg', 'alterado');
        $this->assertCount(1, $legado->verificarArchivos($r['lote']), 'una alteración se detecta');
        $disco->put('legado/galeria-prueba.jpg', 'contenido-galeria');

        $this->assertSame('revertido', $this->aplicador()->revertir($r['lote'])['estado']);
        $despuesDisco = collect($disco->allFiles())->mapWithKeys(fn ($f) => [$f => hash('sha256', $disco->get($f))])->all();
        $this->assertSame($antesDisco, $despuesDisco, 'archivar y restaurar no mueven ni borran archivos');
    }

    public function test_archiva_payload_ids_checksums_y_restaura_todas_las_filas(): void
    {
        $antes = [];
        foreach (array_keys(ArchivoLegado::TABLAS) as $tabla) {
            $antes[$tabla] = DB::table($tabla)->orderBy(ArchivoLegado::TABLAS[$tabla])->get()->map(fn ($fila) => (array) $fila)->all();
        }

        $resultado = $this->aplicador()->aplicar($this->archivoAprobado());
        $this->assertSame('aplicado', $resultado['estado'], implode("\n", $resultado['errores']));
        $this->assertSame(array_sum(array_map('count', $antes)), DB::table('migracion_legado_archivos')->count());

        foreach ($antes as $tabla => $filas) {
            $this->assertSame(0, DB::table($tabla)->count(), "{$tabla} no conserva filas de demostración operativas");
            $archivadas = DB::table('migracion_legado_archivos')->where('tabla_origen', $tabla)->orderBy('id_origen')->get();
            $payloads = $archivadas->map(function ($fila) {
                $this->assertSame($fila->checksum_sha256, hash('sha256', $fila->payload));

                return json_decode($fila->payload, true, 512, JSON_THROW_ON_ERROR);
            })->all();
            $this->assertSame($filas, $payloads, "{$tabla} conserva IDs, valores y referencias a archivos");
        }
        $this->assertSame(0, DB::table('migracion_legado_archivos')->where('tabla_origen', 'evento_categorias')->count());

        $this->assertSame('revertido', $this->aplicador()->revertir($resultado['lote'])['estado']);
        foreach ($antes as $tabla => $filas) {
            $despues = DB::table($tabla)->orderBy(ArchivoLegado::TABLAS[$tabla])->get()->map(fn ($fila) => (array) $fila)->all();
            $this->assertSame($filas, $despues, "{$tabla} se restaura con sus IDs y relaciones originales");
        }
        $this->assertSame($resultado['lote'], DB::table('migracion_legado_archivos')->value('lote'));
        $this->assertNotNull(DB::table('migracion_legado_archivos')->value('restaurado_at'));
    }

    public function test_archivar_configuracion_exige_archivar_todas_sus_imagenes(): void
    {
        $archivo = $this->archivoAprobado();
        $plan = SoloLectura::ejecutar(DB::connection(), fn () => (new SimuladorPoblacion(DB::connection()))->ejecutar());
        $imagenes = collect($plan['registros'])->firstWhere('tabla', 'colegio_imagenes');
        $this->assertNotEmpty($imagenes['dependencias'], json_encode($imagenes));
        foreach ($archivo['grupos'] as &$grupo) {
            if ($grupo['grupo'] === 'imagenes') {
                $grupo['decision'] = 'descartar';
            }
        }
        unset($grupo);

        $resultado = $this->aplicador()->aplicar($archivo);
        $this->assertSame('rechazado', $resultado['estado'], json_encode($resultado));
        $this->assertNotEmpty(array_filter($resultado['errores'], fn ($e) => str_contains($e, 'todas sus imágenes')));
        $this->assertSame(0, DB::table('migracion_legado_archivos')->count());
    }

    public function test_archivo_permite_b8_landing_vacia_y_recuperacion_antes_de_b8(): void
    {
        $resultado = $this->aplicador()->aplicar($this->archivoAprobado());
        $this->assertSame('aplicado', $resultado['estado'], implode("\n", $resultado['errores']));
        $antesB8 = $this->huellasDeTablas();

        $b8 = $this->migracionB8();
        $b8->up();
        $this->assertSame($antesB8, $this->huellasDeTablas(), 'B8 DDL conserva los registros y relaciones de las tablas operativas');
        foreach ([
            'colegio_configuracion', 'colegio_imagenes', 'landing_contenido', 'landing_galeria', 'landing_noticias',
        ] as $tabla) {
            $this->assertFalse(collect(Schema::getColumns($tabla))->firstWhere('name', 'id_institucion')['nullable']);
            $this->assertSame(0, DB::table($tabla)->count());
        }
        $trazaArchivo = DB::table('migracion_pertenencia')->where('lote', $resultado['lote'])->where('accion', 'archivar_sin_propietario')->first();
        $this->assertNotNull($trazaArchivo);
        $this->assertSame('archivar_sin_propietario', $trazaArchivo->accion);

        $this->get('/sitio/colegio-alfa')
            ->assertOk()
            ->assertSee('Bienvenidos a Colegio Alfa')
            ->assertDontSee('Educamos con pasión y propósito')
            ->assertDontSee('Nuestros estudiantes ganan el campeonato');

        try {
            $this->aplicador()->revertir($resultado['lote']);
            $this->fail('restaurar filas sin propietario debe esperar a revertir B8');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('primero debe revertirse B8', $e->getMessage());
        }

        $b8->down();
        $this->assertSame($antesB8, $this->huellasDeTablas(), 'la reversión de B8 tampoco elimina ni altera datos');
        $this->assertSame('revertido', $this->aplicador()->revertir($resultado['lote'])['estado']);
        $this->assertSame(24, DB::table('landing_contenido')->count());
        $this->assertSame(0, DB::table('migracion_legado_archivos')->whereNull('restaurado_at')->count());
    }
}
