<?php

namespace App\Modules\Colegio\Models;

use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Support\EsquemaInstitucional;
use App\Modules\Instituciones\Support\TenantContext;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Configuración institucional (nombre, logo, contacto…) — Etapa 2 · B6:
 * UNA POR COLEGIO. Sustituye al antiguo singleton (fila id 1 compartida por
 * todas las instituciones). La institución sale siempre del servidor:
 *
 *  deInstitucion($id)  esquema expandido → la fila con id_institucion = $id; si
 *                      no tiene, una configuración DERIVADA en memoria con el
 *                      nombre y logo de `instituciones` (nunca la de otro colegio);
 *                      esquema antiguo → la fila única solo si hay UNA institución;
 *                      con varias, derivada (la fila compartida no se atribuye).
 *  actual()            la de la sesión; sin institución (SuperAdmin) → neutra.
 *  (B7) páginas públicas: el sitio usa deInstitucion() de la institución del slug;
 *  sin sesión ni slug (login general, 404) se usa neutra(). No hay «configuración pública por defecto».
 *
 * Las configuraciones existentes NO se asignan automáticamente a ningún colegio.
 * id_institucion no es asignable masivamente.
 */
class ColegioConfiguracion extends Model
{
    protected $table = 'colegio_configuracion';

    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'nombre_colegio',
        'logo',
        'direccion',
        'ciudad',
        'departamento',
        'pais',
        'telefono',
        'email_institucional',
        'sitio_web',
        'descripcion',
    ];

    public static function deInstitucion(?int $idInstitucion): self
    {
        if ($idInstitucion === null) {
            return static::neutra();
        }
        $esquema = EsquemaInstitucional::actual();
        if ($esquema->tieneColumna('colegio_configuracion')) {
            return static::query()->where('id_institucion', $idInstitucion)->orderBy('id_configuracion')->first()
                ?? static::derivadaDe($idInstitucion);
        }
        if ($esquema->totalInstituciones() <= 1) {
            return static::query()->orderBy('id_configuracion')->first() ?? static::derivadaDe($idInstitucion);
        }

        return static::derivadaDe($idInstitucion);
    }

    public static function actual(): self
    {
        return static::deInstitucion(TenantContext::idActual());
    }

    /** Lo que se edita en «Configuración del colegio»: la fila propia, o null si no existe todavía. */
    public static function propiaDe(int $idInstitucion): ?self
    {
        if (! EsquemaInstitucional::actual()->tieneColumna('colegio_configuracion')) {
            return EsquemaInstitucional::actual()->totalInstituciones() <= 1
                ? static::query()->orderBy('id_configuracion')->first()
                : null;
        }

        return static::query()->where('id_institucion', $idInstitucion)->orderBy('id_configuracion')->first();
    }

    /** Sin fila propia: solo nombre y logo del registro de plataforma; nada de otro colegio. */
    public static function derivadaDe(int $idInstitucion): self
    {
        $inst = Institucion::query()->find($idInstitucion);
        $c = new static;
        $c->forceFill(['nombre_colegio' => $inst?->nombre ?? config('app.name'), 'logo' => $inst?->logo]);

        return $c;
    }

    /** Marca de la plataforma (SuperAdmin o contexto sin institución). */
    public static function neutra(): self
    {
        $c = new static;
        $c->forceFill(['nombre_colegio' => config('app.name'), 'logo' => null]);

        return $c;
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ColegioImagen::class, 'id_configuracion', 'id_configuracion')->orderBy('orden');
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo ? Storage::disk('public')->url($this->logo) : null,
        );
    }
}
