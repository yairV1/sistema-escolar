<?php

namespace App\Services;

use App\Models\Acudiente;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Orquesta el registro completo de un estudiante: usuario + estudiante +
 * curso/matrícula + acudiente, en una sola transacción. Extraído del modelo
 * Estudiante porque es un proceso de negocio de varios pasos, no una
 * operación CRUD simple sobre una sola tabla.
 */
class EstudianteRegistroService
{
    private const ID_ROL_ESTUDIANTE = 6;

    /** @return array{id_estudiante:int,codigo_estudiante:string} */
    public static function crear(array $d): array
    {
        return DB::transaction(function () use ($d) {
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

            $estudiante = Estudiante::create([
                'id_usuario' => $usuario->id_usuario,
                'codigo_estudiante' => CodigoUnicoService::generar(Estudiante::class, 'codigo_estudiante', 'EST'),
                'fecha_nacimiento' => $d['fecha_nacimiento'],
                'genero' => $d['genero'],
                'direccion' => $direccionCompleta,
                'eps_seguro' => $d['eps'] ?? null,
                'estado_academico' => 'activo',
                'fecha_ingreso' => now()->toDateString(),
                'observaciones_gral' => $d['condicion_medica'] ?? null,
            ]);

            self::asignarCursoYMatricula($estudiante->id_estudiante, $d);
            self::vincularAcudiente($estudiante->id_estudiante, $d);

            return ['id_estudiante' => $estudiante->id_estudiante, 'codigo_estudiante' => $estudiante->codigo_estudiante];
        });
    }

    public static function actualizar(Estudiante $estudiante, array $d): void
    {
        DB::transaction(function () use ($estudiante, $d) {
            $nombres = trim($d['primer_nombre'].' '.($d['segundo_nombre'] ?? ''));
            $apellidos = trim($d['primer_apellido'].' '.($d['segundo_apellido'] ?? ''));

            $estudiante->usuario->update([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'tipo_documento' => $d['tipo_documento'],
                'numero_documento' => $d['numero_documento'],
                'correo' => $d['correo_estudiante'] ?? $estudiante->usuario->correo,
                'telefono' => $d['telefono_estudiante'] ?? null,
            ]);

            $direccionCompleta = collect([$d['direccion'], $d['barrio'] ?? null, $d['localidad'], $d['ciudad']])
                ->filter()
                ->join(', ');

            $estudiante->update([
                'fecha_nacimiento' => $d['fecha_nacimiento'],
                'genero' => $d['genero'],
                'direccion' => $direccionCompleta,
                'eps_seguro' => $d['eps'] ?? null,
                'observaciones_gral' => $d['condicion_medica'] ?? null,
            ]);

            self::asignarCursoYMatricula($estudiante->id_estudiante, $d);

            if (! empty($d['acudiente_nombres']) && ! empty($d['acudiente_numero_documento'])) {
                self::vincularAcudiente($estudiante->id_estudiante, $d);
            }
        });
    }

    /** Port de Curso::asignarCursoYMatricula() (legacy). */
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

        $parentesco = Estudiante::PARENTESCO_MAP[$d['acudiente_parentesco']] ?? 'otro';
        Acudiente::vincularEstudiante($idEstudiante, $idAcudiente, $parentesco, true);
    }
}
