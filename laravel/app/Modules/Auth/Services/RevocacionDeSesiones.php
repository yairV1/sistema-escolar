<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\DB;

/**
 * Único punto para cortar el acceso ya concedido de una o varias cuentas
 * (Fase 1, P1-1/P1-2). Se usa al desactivar usuarios, suspender instituciones
 * y cambiar roles o permisos.
 *
 *  - sesion_valida_desde = ahora → EnsureSessionFresh cierra las sesiones
 *    iniciadas antes, aunque la cuenta se reactive después;
 *  - remember_token = NULL → ninguna cookie «recordarme» anterior vuelve a
 *    autenticar (un NULL nunca coincide; Laravel genera uno nuevo y propio de
 *    cada usuario en el siguiente login con «recordarme»);
 *  - con el driver de sesión `database`, se borran sus filas de `sessions`
 *    (corte inmediato, sin esperar a la siguiente petición).
 *
 * El estado de la cuenta lo sigue validando EnsureCuentaHabilitada en cada
 * petición: esto solo evita que una sesión vieja sobreviva a una reactivación.
 */
class RevocacionDeSesiones
{
    /** @param  int|array<int>  $idsUsuarios */
    public function usuarios(int|array $idsUsuarios): void
    {
        $ids = array_values(array_unique(array_map('intval', (array) $idsUsuarios)));
        if ($ids === []) {
            return;
        }

        $ahora = now();
        foreach (array_chunk($ids, 500) as $lote) {
            DB::table('usuarios')->whereIn('id_usuario', $lote)->update([
                'sesion_valida_desde' => $ahora,
                'remember_token' => null,
            ]);

            $this->borrarSesionesAlmacenadas(fn ($q) => $q->whereIn('user_id', $lote));
        }
    }

    public function institucion(int $idInstitucion): void
    {
        $this->porConsulta(DB::table('usuarios')->where('id_institucion', $idInstitucion));
    }

    public function rol(int $idRol): void
    {
        $this->porConsulta(DB::table('usuarios')->where('id_rol', $idRol));
    }

    private function porConsulta($consulta): void
    {
        $ids = (clone $consulta)->pluck('id_usuario')->map(fn ($id) => (int) $id)->all();
        $this->usuarios($ids);
    }

    private function borrarSesionesAlmacenadas(\Closure $filtro): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $filtro(DB::connection(config('session.connection'))->table(config('session.table', 'sessions')))->delete();
    }
}
