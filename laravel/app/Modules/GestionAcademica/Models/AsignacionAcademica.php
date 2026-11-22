<?php

namespace App\Modules\GestionAcademica\Models;

use App\Modules\Calificaciones\Models\Actividad;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;

class AsignacionAcademica extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = 'curso';

    protected $table = 'asignaciones_academicas';

    protected $primaryKey = 'id_asignacion';

    public $timestamps = false;

    protected $fillable = [
        'id_profesor',
        'id_materia',
        'id_curso',
        'anio_lectivo',
        'estado',
    ];

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class, 'id_asignacion', 'id_asignacion');
    }

    /**
     * Estudiantes con matrícula activa en el curso de esta asignación: el mismo
     * criterio con el que la planilla y la asistencia arman su lista. Toda
     * escritura de notas o asistencia debe limitarse a estos estudiantes.
     *
     * @param  array<int, int|string>  $ids
     * @return array<int, int>  ids recibidos que NO pertenecen al curso
     */
    public function estudiantesFueraDelCurso(array $ids): array
    {
        $matriculados = $this->curso?->matriculas()
            ->where('estado_matricula', 'activa')
            ->pluck('id_estudiante')
            ->map(fn ($id) => (int) $id)
            ->all() ?? [];

        return array_values(array_diff(array_map('intval', $ids), $matriculados));
    }
}
