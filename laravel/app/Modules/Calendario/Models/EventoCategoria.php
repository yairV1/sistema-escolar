<?php

namespace App\Modules\Calendario\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventoCategoria extends Model
{
    protected $table = 'evento_categorias';

    protected $primaryKey = 'id_categoria';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'color',
        'icono',
        'roles_crear',
        'roles_editar',
        'roles_eliminar',
        'roles_ver',
        'es_sistema',
        'estado',
        'orden',
    ];

    protected $casts = [
        'roles_crear' => 'array',
        'roles_editar' => 'array',
        'roles_eliminar' => 'array',
        'roles_ver' => 'array',
        'es_sistema' => 'boolean',
    ];

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'id_categoria', 'id_categoria');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    /** null en `roles_ver` significa visible para cualquier usuario autenticado. */
    public function esVisibleParaRol(string $rolSlug): bool
    {
        return $this->roles_ver === null || in_array($rolSlug, $this->roles_ver, true);
    }
}
