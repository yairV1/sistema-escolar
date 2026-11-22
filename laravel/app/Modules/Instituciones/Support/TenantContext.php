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

    /**
     * ¿El recurso pertenece a la institución del usuario autenticado?
     * Niega por defecto: si el usuario no tiene institución (SuperAdmin,
     * invitado) o el recurso no la tiene, la respuesta es false.
     */
    public static function esDeLaActual(?int $idInstitucionRecurso): bool
    {
        $actual = static::idActual();

        return $actual !== null
            && $idInstitucionRecurso !== null
            && (int) $idInstitucionRecurso === (int) $actual;
    }

    /**
     * Corta la petición con 404 (no 403, para no confirmar que el recurso
     * existe) si no pertenece a la institución del usuario autenticado.
     * Contención de la Etapa 0: se aplica explícitamente en los controladores
     * hasta que la Etapa 1 centralice la autorización por recurso.
     */
    public static function asegurar(?int $idInstitucionRecurso): void
    {
        abort_unless(static::esDeLaActual($idInstitucionRecurso), 404);
    }
}
