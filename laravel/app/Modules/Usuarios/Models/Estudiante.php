<?php

namespace App\Modules\Usuarios\Models;

use App\Modules\Asistencia\Models\Asistencia;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Matriculas\Models\Matricula;
use App\Modules\Reportes\Models\Boletin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;
use App\Modules\Instituciones\Support\TenantContext;

class Estudiante extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = 'usuario';

    protected $table = 'estudiantes';

    protected $primaryKey = 'id_estudiante';

    public $timestamps = false;

    /** Mapeo form -> enum real de BD (el select del wizard no distingue abuela/tia/hermana). */
    public const PARENTESCO_MAP = [
        'madre' => 'madre', 'padre' => 'padre', 'abuelo' => 'abuelo',
        'tio' => 'tio', 'hermano' => 'hermano', 'acudiente' => 'tutor_legal', 'otro' => 'otro',
    ];

    protected $fillable = [
        'id_usuario',
        'codigo_estudiante',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'eps_seguro',
        'estado_academico',
        'fecha_ingreso',
        'observaciones_gral',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'id_estudiante', 'id_estudiante');
    }

    public function boletines(): HasMany
    {
        return $this->hasMany(Boletin::class, 'id_estudiante', 'id_estudiante');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_estudiante', 'id_estudiante');
    }

    /**
     * Regla de negocio (documentada, ajustable): un estudiante está "en
     * riesgo" si su promedio publicado es menor a 3.5, o si su asistencia
     * está por debajo del 80%. Usada tanto por el dashboard como por Listados.
     *
     * $idInstitucion acota el resultado a una institución (multi-tenant):
     * estudiantes no tiene columna propia, así que se filtra vía el usuario
     * asociado. Null = sin acotar (comportamiento previo a la Fase A).
     */
    public static function idsEnRiesgo(?int $idInstitucion = null): Collection
    {
        // Etapa 1: siempre acotado. Sin institución explícita se usa la del
        // usuario autenticado; sin ninguna, no devuelve nada (antes: todos los
        // colegios).
        $idInstitucion ??= TenantContext::idActual();

        $porPromedio = static::deInstitucion($idInstitucion)
            ->whereHas('boletines', fn ($q) => $q->where('promedio_general', '<', 3.5))
            ->pluck('id_estudiante');

        $porAsistencia = Asistencia::select('id_estudiante')
            ->whereIn('id_estudiante', static::deInstitucion($idInstitucion)->select('id_estudiante'))
            ->groupBy('id_estudiante')
            ->havingRaw('SUM(estado_asistencia = "presente") / COUNT(*) < 0.8')
            ->pluck('id_estudiante');

        return $porPromedio->merge($porAsistencia)->unique();
    }
}
