<?php

namespace App\Modules\Instituciones\Support;

use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

/**
 * Pertenencia a una institución para modelos que (todavía) no tienen columna
 * id_institucion propia: la institución se deriva por una cadena de
 * relaciones hasta un modelo que sí la tiene (Usuario, Curso…).
 *
 * Cada modelo declara la cadena con la constante RUTA_INSTITUCION:
 *   ''                   -> el propio modelo tiene id_institucion
 *   'usuario'            -> $modelo->usuario->id_institucion
 *   'asignacion.curso'   -> $modelo->asignacion->curso->id_institucion
 *
 * Un solo mapa alimenta las tres capas de la Etapa 1: el middleware de rutas
 * (EnsureRecursosDeInstitucion), los listados (scope deInstitucion) y la
 * validación de formularios (TenantGuard::existe). Etapa 2 podrá reemplazar
 * las cadenas por una columna id_institucion sin tocar a los consumidores.
 *
 * Etapa 2 · B5 — modelos con cadena (perfiles, eventos, soportes) cuya tabla
 * YA tiene id_institucion (esquema expandido, ver EsquemaInstitucional):
 *  · al crear, la columna se copia de la institución del dueño (la cadena);
 *    si la sesión es de otra institución, se rechaza; si el dueño no tiene
 *    institución, queda NULL (nunca un valor por defecto);
 *  · al leer, la cadena sigue mandando y, además, la columna debe ser NULL
 *    (aún sin poblar) o coincidir: una columna contradictoria = registro ajeno.
 * En esquema legado (sin columna) el comportamiento es el de la Etapa 1.
 */
trait PerteneceAInstitucion
{
    public static function bootPerteneceAInstitucion(): void
    {
        static::creating(function ($modelo) {
            if (static::RUTA_INSTITUCION === '' || ! EsquemaInstitucional::actual()->tieneColumna($modelo->getTable())) {
                return;
            }
            $delDueno = $modelo->idInstitucionPorRuta();
            $sesion = TenantContext::idActual();
            if ($sesion !== null && $delDueno !== null && $delDueno !== $sesion) {
                throw new RuntimeException('El dueño del registro pertenece a otra institución.');
            }
            $modelo->setAttribute('id_institucion', $delDueno);
        });
    }

    /** Institución derivada por la cadena de relaciones (Etapa 1). */
    public function idInstitucionPorRuta(): ?int
    {
        $ruta = static::RUTA_INSTITUCION;
        $nodo = $this;

        if ($ruta !== '') {
            foreach (explode('.', $ruta) as $relacion) {
                $nodo = $nodo?->{$relacion};
            }
        }

        $id = $nodo?->id_institucion;

        return $id === null ? null : (int) $id;
    }

    /** Institución del registro, o null si no puede determinarse o es incoherente (se tratará como ajeno). */
    public function idInstitucionPropia(): ?int
    {
        $porRuta = $this->idInstitucionPorRuta();
        if (static::RUTA_INSTITUCION !== '' && $porRuta !== null && EsquemaInstitucional::actual()->tieneColumna($this->getTable())) {
            $columna = $this->getAttribute('id_institucion');
            if ($columna !== null && (int) $columna !== $porRuta) {
                return null;   // la columna contradice al dueño: no se confía en ninguna
            }
        }

        return $porRuta;
    }

    /**
     * Limita la consulta a la institución indicada (por defecto, la del usuario
     * autenticado). Sin institución determinable no devuelve nada: falla cerrado.
     */
    public function scopeDeInstitucion(Builder $query, ?int $idInstitucion = null): Builder
    {
        $idInstitucion ??= TenantContext::idActual();

        if ($idInstitucion === null) {
            return $query->whereRaw('1 = 0');
        }

        $ruta = static::RUTA_INSTITUCION;

        if ($ruta === '') {
            return $query->where($query->getModel()->qualifyColumn('id_institucion'), $idInstitucion);
        }

        $query->whereHas($ruta, fn (Builder $q) => $q->where(
            $q->getModel()->qualifyColumn('id_institucion'),
            $idInstitucion
        ));

        // Esquema expandido: la columna propia, si ya tiene valor, debe coincidir.
        if (EsquemaInstitucional::actual()->tieneColumna($query->getModel()->getTable())) {
            $col = $query->getModel()->qualifyColumn('id_institucion');
            $query->where(fn (Builder $q) => $q->whereNull($col)->orWhere($col, $idInstitucion));
        }

        return $query;
    }
}
