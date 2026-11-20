<?php

namespace Tests\Feature\Aislamiento;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\EscenarioDosColegios;
use Tests\TestCase;

/**
 * Base de las pruebas de aislamiento entre instituciones. Cada prueba parte
 * de una base vacía (SQLite en memoria o MariaDB colegio_tests, verificada por
 * GuardiaBaseDePruebas) con EscenarioDosColegios.
 */
abstract class AislamientoTestCase extends TestCase
{
    use RefreshDatabase;

    protected EscenarioDosColegios $e;

    protected function setUp(): void
    {
        parent::setUp();
        $this->e = EscenarioDosColegios::crear();
    }

    /** '{a.estudiante}' -> id del modelo $e->a['estudiante'] (también en rutas). */
    protected function resolver(string $plantilla): string
    {
        $plantilla = str_replace('{superadmin}', (string) $this->e->superadmin->getKey(), $plantilla);

        return preg_replace_callback('/\{([ab])\.(\w+)\}/', function ($m) {
            $modelo = $this->e->{$m[1]}[$m[2]];

            return (string) ($modelo instanceof Model ? $modelo->getKey() : $modelo);
        }, $plantilla);
    }

    protected function actor(string $clave)
    {
        return $clave === 'superadmin' ? $this->e->superadmin : $this->e->{substr($clave, 0, 1)}[substr($clave, 2)];
    }

    /** Huella de una tabla completa para comprobar que una petición rechazada no cambió nada. */
    protected function huella(string $tabla): string
    {
        return md5(json_encode(DB::table($tabla)->orderByRaw('1')->get()));
    }

    /** Resuelve marcadores también dentro de cuerpos de petición (arrays anidados). */
    protected function resolverDatos(array $datos): array
    {
        array_walk_recursive($datos, function (&$v) {
            if (is_string($v) && str_contains($v, '{')) {
                $v = $this->resolver($v);
            }
        });

        return $datos;
    }

    /**
     * Ejecuta un caso de matriz: actor, método, URL, datos, códigos esperados
     * y, opcionalmente, la tabla cuya huella debe (o no) cambiar.
     */
    protected function ejecutarCaso(string $actor, string $metodo, string $url, ?array $datos, array $esperados, ?string $tabla, ?bool $debeCambiar)
    {
        $antes = $tabla ? $this->huella($tabla) : null;
        $this->actingAs($this->actor($actor));
        $url = $this->resolver($url);
        $r = $metodo === 'get' ? $this->get($url) : $this->postJson($url, $this->resolverDatos($datos ?? []));

        $this->assertContains($r->getStatusCode(), $esperados, "HTTP {$r->getStatusCode()} en {$metodo} {$url}");
        if ($tabla) {
            $cambio = $antes !== $this->huella($tabla);
            $this->assertSame($debeCambiar, $cambio, "La tabla {$tabla} ".($cambio ? 'cambió' : 'no cambió')." (esperado: ".($debeCambiar ? 'cambiar' : 'sin cambios').')');
        }

        return $r;
    }
}
