<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class Estudiante extends Model
{
    protected $table = 'estudiantes';

    protected $primaryKey = 'id_estudiante';

    public $timestamps = false;

    private const ID_ROL_ESTUDIANTE = 6;

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
     */
    public static function idsEnRiesgo(): Collection
    {
        $porPromedio = static::whereHas('boletines', fn ($q) => $q->where('promedio_general', '<', 3.5))
            ->pluck('id_estudiante');

        $porAsistencia = Asistencia::select('id_estudiante')
            ->groupBy('id_estudiante')
            ->havingRaw('SUM(estado_asistencia = "presente") / COUNT(*) < 0.8')
            ->pluck('id_estudiante');

        return $porPromedio->merge($porAsistencia)->unique();
    }

    public static function generarCodigoUnico(): string
    {
        do {
            $codigo = sprintf('%s-EST-%04d', date('Y'), static::count() + 1);
        } while (static::where('codigo_estudiante', $codigo)->exists());

        return $codigo;
    }

    /**
     * Port de Estudiante::crear() (legacy): crea usuario + estudiante +
     * matrícula/curso + acudiente en una sola transacción.
     *
     * @return array{id_estudiante:int,codigo_estudiante:string}
     */
    public static function crearCompleto(array $d): array
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($d) {
            $nombres = trim($d['primer_nombre'].' '.($d['segundo_nombre'] ?? ''));
            $apellidos = trim($d['primer_apellido'].' '.($d['segundo_apellido'] ?? ''));
            $correo = $d['correo_estudiante'] ?? ($d['numero_documento'].'@estudiantes.sancristobal.edu.co');

            $usuario = Usuario::create([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'tipo_documento' => $d['tipo_documento'],
                'numero_documento' => $d['numero_documento'],
                'correo' => $correo,
                'telefono' => $d['telefono_estudiante'] ?? null,
                'password' => Hash::make($d['numero_documento']),
                'id_rol' => self::ID_ROL_ESTUDIANTE,
            ]);

            $direccionCompleta = collect([$d['direccion'], $d['barrio'] ?? null, $d['localidad'], $d['ciudad']])
                ->filter()
                ->join(', ');

            $estudiante = static::create([
                'id_usuario' => $usuario->id_usuario,
                'codigo_estudiante' => static::generarCodigoUnico(),
                'fecha_nacimiento' => $d['fecha_nacimiento'],
                'genero' => $d['genero'],
                'direccion' => $direccionCompleta,
                'eps_seguro' => $d['eps'] ?? null,
                'estado_academico' => 'activo',
                'fecha_ingreso' => now()->toDateString(),
                'observaciones_gral' => $d['condicion_medica'] ?? null,
            ]);

            static::asignarCursoYMatricula($estudiante->id_estudiante, $d);
            static::vincularAcudiente($estudiante->id_estudiante, $d);

            return ['id_estudiante' => $estudiante->id_estudiante, 'codigo_estudiante' => $estudiante->codigo_estudiante];
        });
    }

    public function actualizarCompleto(array $d): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($d) {
            $nombres = trim($d['primer_nombre'].' '.($d['segundo_nombre'] ?? ''));
            $apellidos = trim($d['primer_apellido'].' '.($d['segundo_apellido'] ?? ''));

            $this->usuario->update([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'tipo_documento' => $d['tipo_documento'],
                'numero_documento' => $d['numero_documento'],
                'correo' => $d['correo_estudiante'] ?? $this->usuario->correo,
                'telefono' => $d['telefono_estudiante'] ?? null,
            ]);

            $direccionCompleta = collect([$d['direccion'], $d['barrio'] ?? null, $d['localidad'], $d['ciudad']])
                ->filter()
                ->join(', ');

            $this->update([
                'fecha_nacimiento' => $d['fecha_nacimiento'],
                'genero' => $d['genero'],
                'direccion' => $direccionCompleta,
                'eps_seguro' => $d['eps'] ?? null,
                'observaciones_gral' => $d['condicion_medica'] ?? null,
            ]);

            static::asignarCursoYMatricula($this->id_estudiante, $d);

            if (! empty($d['acudiente_nombres']) && ! empty($d['acudiente_numero_documento'])) {
                static::vincularAcudiente($this->id_estudiante, $d);
            }
        });
    }

    /** Port de Estudiante::asignarCursoYMatricula() (legacy). */
    private static function asignarCursoYMatricula(int $idEstudiante, array $d): void
    {
        $grupo = $d['grupo'] ?: 'A';
        $nombreCurso = $d['grado'].$grupo;
        $idCurso = Curso::buscarOCrear(
            $nombreCurso,
            Curso::nivelAcademicoDeGrado($d['grado']),
            $d['jornada'],
            (int) $d['anio_lectivo'],
        );

        $matricula = Matricula::where('id_estudiante', $idEstudiante)
            ->where('anio_lectivo', (int) $d['anio_lectivo'])
            ->first();

        if ($matricula) {
            $matricula->update(['id_curso' => $idCurso, 'observacion' => $d['tipo_matricula'] ?? null]);

            return;
        }

        Matricula::create([
            'id_estudiante' => $idEstudiante,
            'id_curso' => $idCurso,
            'anio_lectivo' => (int) $d['anio_lectivo'],
            'fecha_matricula' => now()->toDateString(),
            'estado_matricula' => 'activa',
            'observacion' => $d['tipo_matricula'] ?? null,
        ]);
    }

    private static function vincularAcudiente(int $idEstudiante, array $d): void
    {
        $partes = explode(' ', trim($d['acudiente_nombres']));
        $apellido = array_pop($partes);
        $nombre = implode(' ', $partes) ?: $apellido;

        $idAcudiente = Acudiente::buscarOCrear([
            'nombres' => $nombre,
            'apellidos' => $apellido,
            'tipo_documento' => $d['acudiente_tipo_documento'],
            'numero_documento' => $d['acudiente_numero_documento'],
            'correo' => $d['acudiente_correo'] ?? null,
            'telefono' => $d['acudiente_telefono'] ?? null,
            'ocupacion' => $d['acudiente_ocupacion'] ?? null,
        ]);

        $parentesco = self::PARENTESCO_MAP[$d['acudiente_parentesco']] ?? 'otro';
        Acudiente::vincularEstudiante($idEstudiante, $idAcudiente, $parentesco, true);
    }
}
