<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Catálogo de permisos de negocio (docs/arquitectura/03-rbac.md, seccion 3).
 * Infraestructura RBAC (Fase 3.1): todavía no se usa para autorizar nada,
 * ningún middleware, controller ni policy la consulta aún.
 */
class Permission extends Model
{
    protected $table = 'permissions';

    protected $primaryKey = 'id_permiso';

    protected $fillable = [
        'slug',
        'modulo',
        'descripcion',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'permission_role',
            'id_permiso',
            'id_rol',
        );
    }
}
