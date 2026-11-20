<?php

namespace App\Modules\Landing\Support;

use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Support\EsquemaInstitucional;
use App\Modules\Instituciones\Support\SlugInstitucion;
use App\Modules\Landing\Models\LandingContenido;
use App\Modules\Landing\Models\LandingGaleria;
use App\Modules\Landing\Models\LandingNoticia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Sitio público por institución — Etapa 2 · B7.
 *
 * La institución se resuelve SOLO en el servidor: por el slug de la ruta
 * (/sitio/{slug}) o por PORTAL_PUBLICO_SLUG para «/». Nunca por defecto.
 *
 * Contenido (textos, noticias, galería), según el esquema:
 *  expandido   → solo las filas con id_institucion = la del sitio. Las filas sin
 *                dueño (contenido compartido previo) no se muestran en ningún sitio.
 *  antiguo, 1  → el contenido único de siempre (no hay a quién confundir).
 *  antiguo, ≥2 → el contenido compartido NO se atribuye a nadie: textos
 *                provisionales genéricos, sin noticias ni galería. Edición bloqueada.
 */
class SitioInstitucional
{
    /** Claves editables de la landing (las mismas que siembra la migración + hero_imagen). */
    public const CLAVES = [
        'hero_badge', 'hero_titulo', 'hero_descripcion', 'hero_stat1_valor', 'hero_stat1_label', 'hero_stat2_valor', 'hero_stat2_label',
        'hero_stat3_valor', 'hero_stat3_label', 'nosotros_texto1', 'nosotros_texto2', 'nosotros_mision', 'nosotros_vision',
        'contacto_direccion', 'contacto_telefono', 'contacto_celular', 'contacto_correo', 'contacto_correo_admisiones', 'contacto_horario',
        'social_facebook', 'social_instagram', 'social_youtube', 'social_whatsapp', 'hero_imagen',
    ];

    /** Formato aceptado de slug (minúsculas, números y guiones simples). Cualquier otra cosa → 404. */
    public const FORMATO_SLUG = SlugInstitucion::FORMATO;

    private const GRADOS = ['PRE' => 'Preescolar', '1' => 'Grado 1°', '2' => 'Grado 2°', '3' => 'Grado 3°', '4' => 'Grado 4°', '5' => 'Grado 5°',
        '6' => 'Grado 6°', '7' => 'Grado 7°', '8' => 'Grado 8°', '9' => 'Grado 9°', '10' => 'Grado 10°', '11' => 'Grado 11°'];

    // ------------------------------------------------------------ resolución
    /** Institución activa por slug canónico o alias legado exacto sin colisión. */
    public static function porSlug(string $slug): Institucion
    {
        abort_unless(strlen($slug) <= 120, 404);
        $inst = Institucion::query()->where('slug', $slug)->first();

        if (SlugInstitucion::esCanonico($slug)) {
            abort_unless($inst !== null && $inst->slug === $slug && $inst->estaActiva(), 404);

            return $inst;
        }

        $slugLegadoCompatible = $inst !== null
            && $inst->slug === $slug
            && SlugInstitucion::normalizar($slug) === mb_strtolower($slug)
            && ! SlugInstitucion::colisiona($slug, $inst->id_institucion);
        abort_unless(
            $slugLegadoCompatible && $inst->estaActiva(),
            404
        );

        return $inst;
    }

    /** Institución del portal raíz («/»), o null si no se configuró ninguna. Configurada pero inválida → 404. */
    public static function portal(): ?Institucion
    {
        $slug = config('sitio.portal_publico_slug');

        return ($slug === null || $slug === '') ? null : static::porSlug((string) $slug);
    }

    // ---------------------------------------------------------------- modo
    public static function expandido(): bool
    {
        $e = EsquemaInstitucional::actual();

        return $e->tieneColumna('landing_contenido') && $e->tieneColumna('landing_noticias') && $e->tieneColumna('landing_galeria');
    }

    /** Esquema antiguo con una sola institución: el contenido único es inequívocamente suyo. */
    private static function unicaInstitucion(): bool
    {
        return ! static::expandido() && EsquemaInstitucional::actual()->totalInstituciones() <= 1;
    }

    public static function motivoBloqueoEdicion(): ?string
    {
        if (! static::expandido() && ! static::unicaInstitucion()) {
            return 'El sitio público todavía es compartido por varias instituciones: editarlo cambiaría el de todas. Se habilita al completar la migración institucional (Etapa 2).';
        }

        return null;
    }

    // ------------------------------------------------------------- contenido
    public static function contenido(Institucion $inst): array
    {
        // Todas las claves presentes (vacías si no hay valor): la vista no debe fallar ni rellenar con nada ajeno.
        $base = array_merge(array_fill_keys(self::CLAVES, ''), static::provisional($inst));
        if (static::expandido()) {
            $propio = LandingContenido::query()->where('id_institucion', $inst->id_institucion)->pluck('valor', 'clave')->all();
        } elseif (static::unicaInstitucion()) {
            $propio = LandingContenido::query()->pluck('valor', 'clave')->all();
        } else {
            $propio = [];
        }

        return array_merge($base, array_filter($propio, fn ($v) => $v !== null && $v !== ''));
    }

    public static function noticias(Institucion $inst, bool $soloActivas = true): Collection
    {
        return static::filas(LandingNoticia::query(), $inst, $soloActivas);
    }

    public static function galeria(Institucion $inst, bool $soloActivas = true): Collection
    {
        return static::filas(LandingGaleria::query(), $inst, $soloActivas);
    }

    private static function filas($consulta, Institucion $inst, bool $soloActivas): Collection
    {
        if (static::expandido()) {
            $consulta->where('id_institucion', $inst->id_institucion);
        } elseif (! static::unicaInstitucion()) {
            return collect();
        }
        if ($soloActivas) {
            $consulta->where('estado', 'activo');
        }

        return $consulta->orderBy('orden')->get();
    }

    /** Textos provisionales seguros: solo el nombre de la propia institución. */
    public static function provisional(Institucion $inst): array
    {
        return [
            'hero_titulo' => 'Bienvenidos a '.$inst->nombre,
            'hero_descripcion' => 'Muy pronto encontrarás aquí la información de nuestro colegio.',
        ];
    }

    /** ¿El registro (noticia, foto, clave) pertenece al sitio de la institución? */
    public static function perteneceA(Model $registro, int $idInstitucion): bool
    {
        if (static::expandido()) {
            return $registro->getAttribute('id_institucion') !== null && (int) $registro->getAttribute('id_institucion') === $idInstitucion;
        }

        return static::unicaInstitucion();
    }

    // ---------------------------------------------------------------- grados
    /**
     * Grados a los que se puede solicitar admisión en ESTA institución: los de sus
     * cursos activos (nombre «7A» → grado 7). Sin cursos registrados: el catálogo
     * general. Nunca los de otra institución.
     */
    public static function grados(Institucion $inst): array
    {
        $grados = Curso::query()->where('id_institucion', $inst->id_institucion)->where('estado', 'activo')->pluck('nombre_curso')
            ->map(fn ($n) => preg_match('/^(PRE|\d{1,2})/i', (string) $n, $m) ? strtoupper($m[1]) : null)
            ->filter(fn ($g) => $g !== null && isset(self::GRADOS[$g]))
            ->unique();

        $claves = $grados->isEmpty() ? array_keys(self::GRADOS) : array_values(array_intersect(array_keys(self::GRADOS), $grados->all()));

        return array_values(array_map(fn ($g) => self::GRADOS[$g], $claves));
    }

    public static function carpeta(int $idInstitucion, string $sub): string
    {
        return static::expandido() ? "landing/{$idInstitucion}/{$sub}" : "landing/{$sub}";
    }
}
