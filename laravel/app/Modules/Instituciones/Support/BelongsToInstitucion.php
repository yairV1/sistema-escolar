<?php

namespace App\Modules\Instituciones\Support;

use App\Modules\Instituciones\Models\Institucion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fase B (pendiente, ver docs/arquitectura/10-superadmin-plataforma.md
 * sección "Fase B"): mecanismo listo para que un módulo de negocio
 * existente (Estudiantes, Matriculas, GestionAcademica...) declare
 * pertenencia a una institución sin repetir la relación a mano. Ningún
 * modelo de negocio lo usa todavía — se aplica módulo a módulo cuando esa
 * fase arranque, igual que el resto de migraciones incrementales de este
 * proyecto.
 */
trait BelongsToInstitucion
{
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    protected static function bootBelongsToInstitucion(): void
    {
        static::creating(function ($modelo) {
            if (! $modelo->id_institucion && $institucion = TenantContext::actual()) {
                $modelo->id_institucion = $institucion->id_institucion;
            }
        });
    }
}
