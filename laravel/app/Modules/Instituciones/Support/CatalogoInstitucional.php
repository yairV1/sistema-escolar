<?php

namespace App\Modules\Instituciones\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Catálogo institucional (Etapa 2 · B5): materias, periodos, tipos de
 * actividad, categorías del calendario. Antes de la Etapa 2 eran globales.
 *
 * Usa PerteneceAInstitucion (para que el middleware de la Etapa 1, TenantGuard
 * y los listados lo traten como recurso institucional) y redefine su
 * semántica según el modo de la tabla (EsquemaInstitucional):
 *
 *  LEGADO (sin columna): el registro es COMPARTIDO por todas las instituciones,
 *    igual que antes. Se puede leer; crearlo o modificarlo afectaría a otros
 *    colegios, así que con más de una institución en la base esas operaciones
 *    se BLOQUEAN hasta completar la migración (no hay forma segura de hacerlas).
 *
 *  EXPANDIDO (con columna):
 *    · id_institucion = X → propio de X; invisible (404) para los demás.
 *    · id_institucion NULL → plantilla global (si la tabla las admite) o
 *      registro aún sin poblar: visible, de SOLO LECTURA.
 *    · Al crear, id_institucion sale SIEMPRE de la sesión autenticada; lo
 *      que llegue en el formulario se ignora. Sin institución: se rechaza.
 *
 * Cada modelo declara: const PLANTILLA_GLOBAL (bool) — si sus filas NULL son
 * plantillas oficiales (evento_categorias, escala_notas) o solo «pendientes».
 */
trait CatalogoInstitucional
{
    use PerteneceAInstitucion;

    public const RUTA_INSTITUCION = '';

    public static function bootCatalogoInstitucional(): void
    {
        static::creating(function (self $modelo) {
            if (! $modelo->tieneColumnaInstitucion()) {
                return;   // legado: el controlador ya verificó que crear sea seguro
            }
            if (Auth::check()) {
                // Petición autenticada: la institución es la de la sesión, siempre.
                $actual = TenantContext::idActual();
                if ($actual === null) {
                    throw new RuntimeException('No se puede crear un registro de catálogo sin institución activa.');
                }
                $modelo->setAttribute('id_institucion', $actual);
            }
            // Sin sesión (consola, seeders, factories): se respeta el valor explícito
            // del código, que puede ser NULL (plantilla). Nunca un valor por defecto.
        });
    }

    public function tieneColumnaInstitucion(): bool
    {
        return EsquemaInstitucional::actual()->tieneColumna($this->getTable());
    }

    /** Legado: todo es compartido. Expandido: lo propio + lo que no tiene dueño (solo lectura). */
    public function scopeDeInstitucion(Builder $query, ?int $idInstitucion = null): Builder
    {
        $idInstitucion ??= TenantContext::idActual();
        if ($idInstitucion === null) {
            return $query->whereRaw('1 = 0');
        }
        if (! $query->getModel()->tieneColumnaInstitucion()) {
            return $query;
        }
        $col = $query->getModel()->qualifyColumn('id_institucion');

        return $query->where(fn (Builder $q) => $q->where($col, $idInstitucion)->orWhereNull($col));
    }

    /** Solo lo que pertenece a la institución (excluye compartidos y plantillas). */
    public function scopePropiosDeInstitucion(Builder $query, ?int $idInstitucion = null): Builder
    {
        $idInstitucion ??= TenantContext::idActual();
        if ($idInstitucion === null || ! $query->getModel()->tieneColumnaInstitucion()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($query->getModel()->qualifyColumn('id_institucion'), $idInstitucion);
    }

    public function idInstitucionPropia(): ?int
    {
        if (! $this->tieneColumnaInstitucion()) {
            return null;
        }
        $id = $this->getAttribute('id_institucion');

        return $id === null ? null : (int) $id;
    }

    /** Lo usa TenantGuard (middleware de rutas y validación): ¿puede la institución VER este registro? */
    public function visibleParaInstitucion(?int $idInstitucion): bool
    {
        if ($idInstitucion === null) {
            return false;
        }

        return ! $this->tieneColumnaInstitucion()
            || $this->getAttribute('id_institucion') === null
            || (int) $this->getAttribute('id_institucion') === $idInstitucion;
    }

    public function esPlantilla(): bool
    {
        return $this->tieneColumnaInstitucion() && static::PLANTILLA_GLOBAL && $this->getAttribute('id_institucion') === null;
    }

    /** ¿Compartido entre instituciones (legado, o expandido aún sin poblar)? */
    public function esCompartido(): bool
    {
        return ! $this->tieneColumnaInstitucion() || ($this->getAttribute('id_institucion') === null && ! static::PLANTILLA_GLOBAL);
    }

    /** Motivo por el que la institución actual NO puede modificar el registro, o null. */
    public function motivoNoModificable(): ?string
    {
        $actual = TenantContext::idActual();
        if ($actual === null) {
            return 'No tiene una institución activa.';
        }
        if ($this->esPlantilla()) {
            return 'Es una plantilla global: solo lectura.';
        }
        if ($this->esCompartido() && EsquemaInstitucional::actual()->totalInstituciones() > 1) {
            return 'Este registro es compartido por varias instituciones y no puede modificarse hasta completar la migración institucional (Etapa 2).';
        }
        if ($this->tieneColumnaInstitucion() && $this->getAttribute('id_institucion') !== null && (int) $this->getAttribute('id_institucion') !== $actual) {
            return 'Pertenece a otra institución.';
        }

        return null;
    }

    /** Motivo por el que NO se puede crear un registro de este catálogo, o null. */
    public static function motivoNoCreable(): ?string
    {
        if (TenantContext::idActual() === null) {
            return 'No tiene una institución activa.';
        }
        $tabla = (new static)->getTable();
        if (! EsquemaInstitucional::actual()->tieneColumna($tabla) && EsquemaInstitucional::actual()->totalInstituciones() > 1) {
            return 'Este catálogo todavía es compartido entre instituciones: un registro nuevo sería visible y utilizable por todas. La creación se habilita al completar la migración institucional (Etapa 2).';
        }

        return null;
    }

    /** Corta la petición (409; 403 para plantillas) si no se puede modificar. */
    public function asegurarModificable(): void
    {
        if ($motivo = $this->motivoNoModificable()) {
            abort(response()->json(['success' => false, 'message' => $motivo], $this->esPlantilla() ? 403 : 409));
        }
    }

    public static function asegurarCreable(): void
    {
        if ($motivo = static::motivoNoCreable()) {
            abort(response()->json(['success' => false, 'message' => $motivo], 409));
        }
    }
}
