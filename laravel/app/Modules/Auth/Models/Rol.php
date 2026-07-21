<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Solo lectura para CRUD de negocio: la tabla `roles` no tiene CRUD propio
 * todavía, el mapeo id_rol -> slug vive en Usuario::ROLE_SLUGS.
 *
 * La relación `permissions()` es infraestructura RBAC (Fase 3.1, ver
 * docs/arquitectura/03-rbac.md, seccion 10) y todavía no se usa para
 * autorizar nada: ningún middleware, controller ni policy la consulta
 * aún.
 */
class Rol extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_rol';

    public $timestamps = false;

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role',
            'id_rol',
            'id_permiso',
        );
    }
}
