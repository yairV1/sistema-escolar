<?php

namespace App\Modules\Planes\Models;

use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Modulos\Models\Modulo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de planes comerciales de la plataforma (ver migración
 * create_planes_table). Vive en su propio módulo de dominio, no bajo
 * SuperAdmin, siguiendo el mismo criterio que `Institucion`: el
 * controlador que lo expone vive en `SuperAdmin\Controllers`, el modelo
 * vive donde otros módulos podrían necesitarlo más adelante.
 */
class Plan extends Model
{
    protected $table = 'planes';

    protected $primaryKey = 'id_plan';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'precio_mensual',
        'precio_anual',
        'limite_usuarios',
        'beneficios',
        'estado',
        'orden',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'precio_anual' => 'decimal:2',
        'beneficios' => 'array',
    ];

    public function modulos(): BelongsToMany
    {
        return $this->belongsToMany(Modulo::class, 'plan_modulo', 'id_plan', 'id_modulo');
    }

    public function instituciones(): HasMany
    {
        return $this->hasMany(Institucion::class, 'id_plan', 'id_plan');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }
}
