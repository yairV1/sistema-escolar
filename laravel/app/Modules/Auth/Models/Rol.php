<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Solo lectura: la tabla `roles` no tiene CRUD propio todavía,
 * el mapeo id_rol -> slug vive en Usuario::ROLE_SLUGS.
 */
class Rol extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_rol';

    public $timestamps = false;
}
