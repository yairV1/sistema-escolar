<?php

namespace App\Modules\Instituciones\Models;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Planes\Models\Plan;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Registro de tenants de la plataforma (Fase A del pivote multi-tenant,
 * docs/arquitectura/10-superadmin-plataforma.md). Institución #1 es el
 * colegio que ya operaba este sistema antes del pivote (ver migración
 * create_instituciones_table).
 *
 * Este módulo es intencionalmente el único punto de dependencia que otros
 * módulos de negocio necesitarán en la Fase B (aislar sus propias tablas
 * por id_institucion vía Support\BelongsToInstitucion) — mismo motivo por
 * el que vive separado del módulo SuperAdmin (el panel), que solo
 * orquesta sobre este dominio.
 */
class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $primaryKey = 'id_institucion';

    protected $fillable = [
        'nombre',
        'slug',
        'nit',
        'email_contacto',
        'telefono',
        'direccion',
        'ciudad',
        'pais',
        'logo',
        'plan',
        'id_plan',
        'limite_usuarios',
        'estado',
        'fecha_inicio',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_institucion', 'id_institucion');
    }

    /** Nombrada distinto de la columna `plan` (string legado) para no colisionar con ese atributo. */
    public function planCatalogo(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }

    /** Mismo patrón que ColegioConfiguracion::logoUrl(). Null si la institución no cargó logo — el sidebar cae al ícono por defecto. */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo ? Storage::disk('public')->url($this->logo) : null,
        );
    }

    public function estaActiva(): bool
    {
        return $this->estado === 'activa';
    }

    public function proximaAVencer(int $dias = 30): bool
    {
        return $this->fecha_vencimiento !== null
            && $this->fecha_vencimiento->isFuture()
            && now()->diffInDays($this->fecha_vencimiento, false) <= $dias;
    }

    public function vencida(): bool
    {
        return $this->fecha_vencimiento !== null && $this->fecha_vencimiento->isPast();
    }

    /**
     * Slugs de módulos disponibles para esta institución: los del plan
     * asignado que además siguen activos en el catálogo global (si el
     * SuperAdmin desactiva un módulo, desaparece de aquí para todas las
     * instituciones cuyo plan lo incluía). Null = sin plan asignado, no se
     * restringe nada (compatibilidad con instituciones sin catálogo) —
     * distinto de una colección vacía, que sí bloquearía todo.
     */
    public function modulosActivosSlugs(): ?\Illuminate\Support\Collection
    {
        if (! $this->id_plan) {
            return null;
        }

        return $this->planCatalogo?->modulos()->where('modulos.estado', 'activo')->pluck('slug') ?? collect();
    }

    public function tieneModuloActivo(string $slug): bool
    {
        $slugs = $this->modulosActivosSlugs();

        return $slugs === null || $slugs->contains($slug);
    }
}
