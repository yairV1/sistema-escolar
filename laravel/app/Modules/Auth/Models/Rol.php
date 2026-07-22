<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * CRUD de metadata (nombre_rol, descripcion, estado) desde el panel de
 * administración (RolController). No se pueden crear ni eliminar filas:
 * los 7 roles institucionales están fijos y el mapeo id_rol -> slug sigue
 * viviendo en Usuario::ROLE_SLUGS, no aquí.
 *
 * `estado` es informativo por ahora: ningún middleware ni lógica de
 * negocio lo consulta todavía para bloquear accesos.
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

    protected $fillable = [
        'nombre_rol',
        'descripcion',
        'estado',
    ];

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
