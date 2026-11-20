<?php

namespace Tests\Feature\Aislamiento;

use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Landing\Models\LandingGaleria;
use App\Modules\Landing\Models\LandingNoticia;
use Database\Factories\InstitucionFactory;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

/**
 * Etapa 2 · B7 — sitio público y admisiones por colegio (esquema expandido),
 * dos colegios ficticios: colegio-alfa (cursos 6P, 7A) y colegio-zeta (curso 9Z).
 * El contenido compartido que siembran las migraciones queda sin dueño y no
 * debe aparecer en ningún sitio.
 */
class Etapa2B7SitioPublicoTest extends AislamientoTestCase
{
    /** Textos del contenido compartido sembrado por las migraciones. */
    private const COMPARTIDO = ['Educamos con pasión y propósito', 'Nuestros estudiantes ganan el campeonato', '(601) 234-5678'];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        RateLimiter::clear('admision:127.0.0.1|colegio-zeta');
    }

    private function a()
    {
        return $this->e->a['directivo'];
    }

    private function b()
    {
        return $this->e->b['directivo'];
    }

    private function inst(string $c): int
    {
        return $this->e->{$c}['institucion']->id_institucion;
    }

    private function publicar($actor, string $titulo, string $noticia): void
    {
        $this->actingAs($actor)->postJson(route('editar-landing.contenido.update'), ['valores' => ['hero_titulo' => $titulo, 'contacto_telefono' => $titulo.' tel', 'clave_inventada' => 'x']])->assertOk();
        $this->actingAs($actor)->post(route('editar-landing.noticias.store'), ['titulo' => $noticia, 'descripcion' => 'd', 'etiqueta' => 'e', 'fecha' => '2026-11-10', 'orden' => 1], ['Accept' => 'application/json'])->assertOk();
        $this->actingAs($actor)->post(route('editar-landing.galeria.store'), ['imagen' => UploadedFile::fake()->image('g.jpg'), 'descripcion' => 'Foto '.$noticia, 'orden' => 1], ['Accept' => 'application/json'])->assertOk();
        auth()->logout();
    }

    private function admision(string $slug, array $extra = [])
    {
        return $this->postJson("/sitio/{$slug}/admision", array_merge([
            'nombre_acudiente' => 'Marta', 'apellido_acudiente' => 'Pruebas', 'correo' => 'familia.ficticia@example.test', 'telefono' => '3000000000',
            'nombre_estudiante' => 'Niño Ficticio', 'grado_interes' => 'Grado 9°', 'mensaje' => 'Hola',
        ], $extra));
    }

    // ------------------------------------------------------------- 1, 3, 4
    public function test_sitios_independientes_sin_contenido_cruzado_ni_compartido(): void
    {
        $this->publicar($this->a(), 'Alfa Excelencia', 'Noticia Alfa');
        $this->publicar($this->b(), 'Zeta Futuro', 'Noticia Zeta');

        $alfa = $this->get('/sitio/colegio-alfa')->assertOk();
        $alfa->assertSee('Alfa Excelencia')->assertSee('Noticia Alfa')->assertSee('Foto Noticia Alfa')->assertSee('Colegio Alfa');
        $alfa->assertDontSee('Zeta')->assertDontSee('OtroColegio');
        $zeta = $this->get('/sitio/colegio-zeta')->assertOk();
        $zeta->assertSee('Zeta Futuro')->assertSee('Noticia Zeta')->assertDontSee('Alfa');
        foreach (self::COMPARTIDO as $texto) {
            $alfa->assertDontSee($texto);
            $zeta->assertDontSee($texto);
        }
        // Las claves inventadas no se guardan
        $this->assertFalse(DB::table('landing_contenido')->where('clave', 'clave_inventada')->exists());
        // El contenido compartido sigue intacto y sin dueño
        $this->assertSame(24, DB::table('landing_contenido')->whereNull('id_institucion')->count());
    }

    public function test_sin_contenido_propio_muestra_un_estado_provisional_seguro(): void
    {
        $this->publicar($this->a(), 'Alfa Excelencia', 'Noticia Alfa');
        $r = $this->get('/sitio/colegio-zeta')->assertOk()->assertSee('Bienvenidos a Colegio Zeta');
        $r->assertDontSee('Alfa Excelencia')->assertDontSee('Noticia Alfa');
        foreach (self::COMPARTIDO as $texto) {
            $r->assertDontSee($texto);
        }
    }

    // ----------------------------------------------------------------- 2
    public function test_slugs_invalidos_inexistentes_inactivos_y_duplicados(): void
    {
        foreach (['no-existe', 'Colegio-Alfa', 'colegio_alfa', 'colegio--alfa', '-colegio-alfa'] as $slug) {
            $this->get('/sitio/'.$slug)->assertNotFound();
            $this->admision($slug)->assertNotFound();
        }
        Institucion::where('slug', 'colegio-zeta')->update(['estado' => 'suspendida']);
        $this->get('/sitio/colegio-zeta')->assertNotFound();
        $this->admision('colegio-zeta')->assertNotFound();
        $this->assertSame(0, DB::table('solicitudes_admision')->where('correo', 'familia.ficticia@example.test')->count());

        $this->expectException(QueryException::class);
        InstitucionFactory::new()->create(['nombre' => 'Copia', 'slug' => 'colegio-alfa']);
    }

    public function test_raiz_neutral_o_portal_configurado_y_validado(): void
    {
        $r = $this->get('/')->assertOk()->assertSee(config('app.name'));
        foreach (['Colegio Alfa', 'Colegio Zeta', 'San Cristóbal', 'Educamos con pasión'] as $nombre) {
            $r->assertDontSee($nombre);
        }
        config(['sitio.portal_publico_slug' => 'colegio-zeta']);
        $this->get('/')->assertOk()->assertSee('Bienvenidos a Colegio Zeta')->assertDontSee('Colegio Alfa');
        config(['sitio.portal_publico_slug' => 'no-existe']);
        $this->get('/')->assertNotFound();
    }

    public function test_login_general_neutral_e_institucional_con_su_marca(): void
    {
        $this->get('/login')->assertOk()->assertSee(config('app.name'))->assertDontSee('Colegio Alfa')->assertDontSee('San Cristóbal');
        $this->get('/sitio/colegio-alfa/ingresar')->assertOk()->assertSee('Colegio Alfa')->assertDontSee('Colegio Zeta');
        $this->get('/sitio/no-existe/ingresar')->assertNotFound();
    }

    public function test_slug_legacy_en_mayusculas_conserva_su_url_si_no_colisiona(): void
    {
        $institucion = $this->e->a['institucion'];
        $institucion->update(['slug' => 'Privada']);

        $this->get('/sitio/Privada')->assertOk()->assertSee('Colegio Alfa');
        $this->get('/sitio/privada')->assertNotFound();
        $this->assertSame('Privada', $institucion->fresh()->slug);
    }

    // ----------------------------------------------------------------- 5
    public function test_edicion_institucional_solo_de_lo_propio(): void
    {
        $this->publicar($this->a(), 'Alfa Excelencia', 'Noticia Alfa');
        $noticiaA = LandingNoticia::where('titulo', 'Noticia Alfa')->firstOrFail();
        $fotoA = LandingGaleria::where('descripcion', 'Foto Noticia Alfa')->firstOrFail();
        $this->assertSame($this->inst('a'), (int) $noticiaA->id_institucion);
        $this->assertStringStartsWith('landing/'.$this->inst('a').'/', $fotoA->imagen);

        $this->actingAs($this->b())->post(route('editar-landing.noticias.update', $noticiaA->id_noticia), ['titulo' => 'Robada', 'descripcion' => 'd', 'etiqueta' => 'e', 'fecha' => '2026-11-10', 'orden' => 1], ['Accept' => 'application/json'])->assertNotFound();
        $this->actingAs($this->b())->postJson(route('editar-landing.noticias.desactivar', $noticiaA->id_noticia))->assertNotFound();
        $this->actingAs($this->b())->postJson(route('editar-landing.galeria.desactivar', $fotoA->id_foto))->assertNotFound();
        $this->assertSame('Noticia Alfa', $noticiaA->fresh()->titulo);
        $this->assertSame('activo', $fotoA->fresh()->estado);

        // Lo compartido (sin dueño) no es editable por ningún colegio
        $compartida = LandingNoticia::whereNull('id_institucion')->firstOrFail();
        $this->actingAs($this->a())->postJson(route('editar-landing.noticias.desactivar', $compartida->id_noticia))->assertNotFound();

        // El editor de Zeta no muestra lo de Alfa; editar Zeta no toca Alfa
        $this->actingAs($this->b())->get(route('editar-landing.index', ['tab' => 'noticias']))->assertOk()->assertDontSee('Noticia Alfa');
        $this->actingAs($this->b())->postJson(route('editar-landing.contenido.update'), ['valores' => ['hero_titulo' => 'Zeta']])->assertOk();
        $this->assertSame('Alfa Excelencia', DB::table('landing_contenido')->where('id_institucion', $this->inst('a'))->where('clave', 'hero_titulo')->value('valor'));

        $this->actingAs($this->e->superadmin)->get(route('editar-landing.index'))->assertForbidden();
    }

    // ---------------------------------------------------------- 6, 7, 8, 9
    public function test_admisiones_por_colegio_con_institucion_del_servidor(): void
    {
        $r = $this->admision('colegio-zeta', ['id_institucion' => $this->inst('a')])->assertOk()->assertJsonPath('success', true);
        $this->assertStringNotContainsString('familia.ficticia', $r->getContent(), 'la respuesta no devuelve datos personales');
        $this->assertStringNotContainsString('Niño Ficticio', $r->getContent());
        $fila = DB::table('solicitudes_admision')->where('correo', 'familia.ficticia@example.test')->first();
        $this->assertSame($this->inst('b'), (int) $fila->id_institucion, 'la institución sale de la ruta, no del formulario');

        // Grado que solo ofrece Alfa (7°) → rechazado en Zeta
        $this->admision('colegio-zeta', ['grado_interes' => 'Grado 7°', 'correo' => 'otra@example.test'])->assertStatus(422)->assertJsonValidationErrors('grado_interes');
        $this->assertSame(0, DB::table('solicitudes_admision')->where('correo', 'otra@example.test')->count());
        $this->admision('colegio-alfa', ['grado_interes' => 'Grado 7°', 'correo' => 'alfa7@example.test'])->assertOk();
        $this->assertSame($this->inst('a'), (int) DB::table('solicitudes_admision')->where('correo', 'alfa7@example.test')->value('id_institucion'));

        // Campo trampa: respuesta normal, nada guardado
        $this->admision('colegio-alfa', ['grado_interes' => 'Grado 7°', 'correo' => 'bot@example.test', 'sitio_web_contacto' => 'http://spam'])->assertOk();
        $this->assertSame(0, DB::table('solicitudes_admision')->where('correo', 'bot@example.test')->count());

        // Ruta antigua: sin portal configurado → 404 (antes: siempre institución 1)
        $this->postJson('/solicitudes-admision', ['nombre_acudiente' => 'X', 'apellido_acudiente' => 'Y', 'correo' => 'antigua@example.test', 'telefono' => '1', 'nombre_estudiante' => 'Z', 'grado_interes' => 'Grado 9°'])->assertNotFound();
        $this->assertSame(0, DB::table('solicitudes_admision')->where('correo', 'antigua@example.test')->count());

        // Datos personales: no aparecen en los sitios ni en el panel del otro colegio
        $this->get('/sitio/colegio-zeta')->assertDontSee('familia.ficticia');
        $this->actingAs($this->a())->get(route('matriculas'))->assertOk()->assertDontSee('familia.ficticia')->assertSee('alfa7@example.test');
    }

    public function test_limite_de_solicitudes_abusivas(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->admision('colegio-zeta', ['correo' => "f{$i}@example.test"])->assertOk();
        }
        $this->admision('colegio-zeta', ['correo' => 'f6@example.test'])->assertStatus(429);
        $this->assertSame(0, DB::table('solicitudes_admision')->where('correo', 'f6@example.test')->count());
    }
}
