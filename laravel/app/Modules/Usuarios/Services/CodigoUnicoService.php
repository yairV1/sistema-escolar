<?php

namespace App\Modules\Usuarios\Services;

/**
 * Genera códigos únicos con el patrón "AAAA-PREFIJO-NNNN" (año + secuencial),
 * verificando colisión contra la tabla real. Antes duplicado casi línea por
 * línea entre Estudiante::generarCodigoUnico() y Profesor::generarCodigoUnico().
 */
class CodigoUnicoService
{
    /**
     * @param class-string $modelo Clase Eloquent sobre la que se cuenta/verifica.
     */
    public static function generar(string $modelo, string $columna, string $prefijo): string
    {
        do {
            $codigo = sprintf('%s-%s-%04d', date('Y'), $prefijo, $modelo::count() + 1);
        } while ($modelo::where($columna, $codigo)->exists());

        return $codigo;
    }
}
