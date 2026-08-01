<?php

namespace App\Modules\Instituciones\Support;

use App\Modules\Instituciones\Models\Institucion;
use Illuminate\Support\Facades\Auth;

/**
 * Resuelve la institución del usuario autenticado. Único punto de acceso
 * a "cuál es el tenant actual" — cuando la Fase B aplique
 * BelongsToInstitucion a otros módulos, deben resolver el tenant a través
 * de aquí, no repetir `auth()->user()->id_institucion` disperso por el
 * código (docs/arquitectura/08-estandares.md §3.3, DRY).
 *
 * Devuelve null para el SuperAdmin (no pertenece a ninguna institución) y
 * para peticiones sin usuario autenticado.
 */
class TenantContext
{
    public static function actual(): ?Institucion
    {
        $usuario = Auth::user();

        return $usuario?->institucion;
    }

    public static function idActual(): ?int
    {
        return Auth::user()?->id_institucion;
    }
}
