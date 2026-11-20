<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Instituciones\Support\SlugInstitucion;
use Database\Factories\InstitucionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Etapa2B8SlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_normaliza_privada_sin_modificar_el_valor_original(): void
    {
        $this->assertSame('privada', SlugInstitucion::normalizar('Privada'));
        $this->assertFalse(SlugInstitucion::esCanonico('Privada'));
        $this->assertTrue(SlugInstitucion::esCanonico('colegio-privada-2'));
        $this->assertFalse(SlugInstitucion::esCanonico('colegio_privada'));
    }

    public function test_detecta_colision_independientemente_de_mayusculas(): void
    {
        $institucion = InstitucionFactory::new()->create(['slug' => 'Privada']);

        $this->assertTrue(SlugInstitucion::colisiona('privada'));
        $this->assertFalse(SlugInstitucion::colisiona('Privada', $institucion->id_institucion));
        $this->assertSame('Privada', $institucion->fresh()->slug);

        $errores = [];
        SlugInstitucion::regla()('slug', 'privada', function ($mensaje) use (&$errores) {
            $errores[] = $mensaje;
        });
        $this->assertNotEmpty($errores);

        $errores = [];
        SlugInstitucion::regla($institucion->id_institucion)('slug', 'Privada', function ($mensaje) use (&$errores) {
            $errores[] = $mensaje;
        });
        $this->assertSame([], $errores, 'Se permite conservar un slug legado sin normalizarlo.');
    }

    public function test_detecta_colision_tras_transliterar_caracteres(): void
    {
        InstitucionFactory::new()->create(['slug' => 'Privadá']);

        $this->assertSame('privada', SlugInstitucion::normalizar('Privadá'));
        $this->assertTrue(SlugInstitucion::colisiona('privada'));
    }
}
