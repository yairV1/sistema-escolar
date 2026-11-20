<?php

namespace App\Modules\GestionAcademica\Models;

use App\Modules\Instituciones\Support\BelongsToInstitucion;
use App\Modules\Matriculas\Models\Matricula;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Instituciones\Support\PerteneceAInstitucion;
use App\Modules\Instituciones\Support\TenantContext;
use Illuminate\Validation\ValidationException;

class Curso extends Model
{
    use PerteneceAInstitucion;

    /** Cadena de relaciones hasta la institución (ver PerteneceAInstitucion). */
    public const RUTA_INSTITUCION = '';

    use BelongsToInstitucion;

    protected $table = 'cursos';

    protected $primaryKey = 'id_curso';

    public $timestamps = false;

    // id_institucion no es asignable en masa: lo fija BelongsToInstitucion al crear.
    protected $fillable = [
        'nombre_curso',
        'nivel_academico',
        'jornada',
        'anio_lectivo',
        'id_director_grupo',
        'estado',
    ];

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'id_curso', 'id_curso');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionAcademica::class, 'id_curso', 'id_curso');
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'id_director_grupo', 'id_profesor');
    }

    private const NIVEL_POR_GRADO = [
        'PRE' => 'preescolar',
        '1' => 'primaria', '2' => 'primaria', '3' => 'primaria', '4' => 'primaria', '5' => 'primaria',
        '6' => 'secundaria', '7' => 'secundaria', '8' => 'secundaria', '9' => 'secundaria',
        '10' => 'media', '11' => 'media',
    ];

    public static function nivelAcademicoDeGrado(string $grado): string
    {
        return self::NIVEL_POR_GRADO[$grado] ?? 'primaria';
    }

    /**
     * Port de Curso::buscarOCrear() (legacy), acotado a la institución (Etapa 1).
     *
     * Antes buscaba por (nombre_curso, anio_lectivo) en TODOS los colegios y
     * reutilizaba el curso de otra institución: un estudiante del colegio B
     * quedaba matriculado en el "7A" del colegio A.
     *
     * Ahora: busca solo en la institución actual. Si no existe y el nombre ya
     * está ocupado por otra institución, la restricción única GLOBAL
     * (nombre_curso, anio_lectivo, jornada) impide crearlo: se bloquea la
     * operación con un error de validación controlado (la transacción del
     * registro se revierte). Se elimina en la Etapa 2, cuando la restricción
     * incluya id_institucion. Nunca se asigna una institución por defecto.
     */
    public static function buscarOCrear(string $nombreCurso, string $nivelAcademico, string $jornada, int $anioLectivo): int
    {
        $idInstitucion = TenantContext::idActual();

        if ($idInstitucion === null) {
            throw ValidationException::withMessages([
                'grado' => 'No se pudo determinar la institución del usuario para asignar el curso.',
            ]);
        }

        $curso = static::deInstitucion($idInstitucion)
            ->where('nombre_curso', $nombreCurso)
            ->where('anio_lectivo', $anioLectivo)
            ->first();

        if ($curso) {
            return $curso->id_curso;
        }

        $ocupadoPorOtra = static::query()
            ->where('nombre_curso', $nombreCurso)
            ->where('anio_lectivo', $anioLectivo)
            ->where('jornada', $jornada)
            ->exists();

        if ($ocupadoPorOtra) {
            throw ValidationException::withMessages([
                'grado' => "No es posible crear el curso {$nombreCurso} ({$anioLectivo}, jornada {$jornada}) en este momento. "
                    .'Crea el curso desde Gestión académica o comunícate con el soporte de la plataforma.',
            ]);
        }

        $curso = static::create([
            'nombre_curso' => $nombreCurso,
            'nivel_academico' => $nivelAcademico,
            'jornada' => $jornada,
            'anio_lectivo' => $anioLectivo,
            'estado' => 'activo',
        ]);

        return $curso->id_curso;
    }
}
