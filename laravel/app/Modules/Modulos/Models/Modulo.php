<?php

namespace App\Modules\Modulos\Models;

use App\Modules\Planes\Models\Plan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Catálogo de módulos funcionales ofrecidos por la plataforma (ver
 * migración create_modulos_table). Puramente informativo/comercial por
 * ahora — no controla activación real de funcionalidad en los módulos de
 * negocio existentes (eso es Fase B, docs/arquitectura/10-superadmin-plataforma.md §2).
 */
class Modulo extends Model
{
    protected $table = 'modulos';

    protected $primaryKey = 'id_modulo';

    protected $fillable = [
        'nombre',
        'slug',
        'categoria',
        'descripcion',
        'icono',
        'estado',
        'orden',
    ];

    public function planes(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_modulo', 'id_modulo', 'id_plan');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }
}
