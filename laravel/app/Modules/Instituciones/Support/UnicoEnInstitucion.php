<?php

namespace App\Modules\Instituciones\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Regla de validación de unicidad para catálogos institucionales (B5),
 * coherente con el índice REAL de la tabla:
 *
 *  - Índice único global (esquema legado, o B2 sin R2a): el motor rechazaría
 *    cualquier repetido en toda la tabla → se comprueba en toda la tabla.
 *  - Índice compuesto con id_institucion (R2a): se comprueba entre lo que la
 *    institución VE (lo propio + compartidos + plantillas), para no tener dos
 *    «Matemáticas» visibles a la vez. Otras instituciones pueden repetirlo.
 *
 * El mensaje es el mismo en ambos casos: no revela qué hay en otros colegios.
 * Uso: 'nombre_materia' => [..., UnicoEnInstitucion::regla(Materia::class, ['nombre_materia'], $this->route('materia'))]
 * Columnas adicionales (p. ej. anio_lectivo) se leen de la misma petición.
 *
 * @param  class-string<Model>  $modelo
 */
class UnicoEnInstitucion
{
    public static function regla(string $modelo, array $columnas, mixed $ignorar = null, string $mensaje = 'Ya existe un registro con ese nombre.'): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fallar) use ($modelo, $columnas, $ignorar, $mensaje) {
            $tabla = (new $modelo)->getTable();
            $consulta = $modelo::query();
            foreach ($columnas as $col) {
                $consulta->where($col, $col === $atributo ? $valor : request()->input($col));
            }
            if ($ignorar instanceof Model) {
                $consulta->whereKeyNot($ignorar->getKey());
            }
            $esquema = EsquemaInstitucional::actual();
            if ($esquema->tieneColumna($tabla) && ! $esquema->tieneUnicoGlobal($tabla, $columnas)) {
                $consulta->deInstitucion();
            }
            if ($consulta->exists()) {
                $fallar($mensaje);
            }
        };
    }
}
