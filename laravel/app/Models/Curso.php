<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $table = 'cursos';

    protected $primaryKey = 'id_curso';

    public $timestamps = false;

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

    /** Port de Curso::buscarOCrear() (legacy): busca por (nombre_curso, anio_lectivo); crea si no existe. */
    public static function buscarOCrear(string $nombreCurso, string $nivelAcademico, string $jornada, int $anioLectivo): int
    {
        $curso = static::where('nombre_curso', $nombreCurso)
            ->where('anio_lectivo', $anioLectivo)
            ->first();

        if ($curso) {
            return $curso->id_curso;
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
