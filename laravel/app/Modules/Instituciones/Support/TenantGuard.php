<?php

namespace App\Modules\Instituciones\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Punto central de autorización por institución (Etapa 1).
 *
 * - perteneceAlUsuario(): ¿un modelo concreto es de la institución actual?
 * - asegurar():           lo mismo, cortando con 404 si no.
 * - existe():             regla de validación para ids que llegan en formularios.
 *
 * Los catálogos todavía globales (materias, periodos, tipos de actividad,
 * categorías, roles, landing) NO usan PerteneceAInstitucion y por tanto no se
 * filtran aquí: dejarán de ser globales en la Etapa 2.
 */
class TenantGuard
{
    public static function esInstitucional(mixed $valor): bool
    {
        return $valor instanceof Model
            && in_array(PerteneceAInstitucion::class, class_uses_recursive($valor), true);
    }

    public static function perteneceAlUsuario(Model $modelo): bool
    {
        // Catálogos (Etapa 2 · B5): también son visibles los compartidos y las plantillas;
        // si se pueden MODIFICAR lo decide el propio catálogo (asegurarModificable).
        if (method_exists($modelo, 'visibleParaInstitucion')) {
            return $modelo->visibleParaInstitucion(TenantContext::idActual());
        }

        return TenantContext::esDeLaActual($modelo->idInstitucionPropia());
    }

    /** 404 (sin revelar existencia) si el recurso no es de la institución actual. */
    public static function asegurar(Model $modelo): void
    {
        abort_unless(static::perteneceAlUsuario($modelo), 404);
    }

    /**
     * Regla de validación: el id debe existir DENTRO de la institución actual.
     * Uso: 'id_estudiante' => ['required', 'integer', TenantGuard::existe(Estudiante::class)]
     *
     * @param  class-string<Model>  $modelo
     */
    public static function existe(string $modelo, ?Closure $restriccion = null): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fallar) use ($modelo, $restriccion) {
            $consulta = $modelo::query()->deInstitucion()->whereKey($valor);

            if ($restriccion) {
                $restriccion($consulta);
            }

            if (! $consulta->exists()) {
                $fallar('El valor seleccionado no es válido.');
            }
        };
    }
}
