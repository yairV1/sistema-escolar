<?php

namespace App\Modules\Usuarios\Services;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Orquesta el registro completo de un docente: usuario + profesor, en una
 * sola transacción. Extraído del modelo Profesor por el mismo motivo que
 * EstudianteRegistroService.
 */
class DocenteRegistroService
{
    private const ID_ROL_DOCENTE = 5;

    /** @return array{id_profesor:int,codigo_profesor:string} */
    public static function crear(array $d): array
    {
        return DB::transaction(function () use ($d) {
            $nombres = trim($d['primer_nombre'].' '.($d['segundo_nombre'] ?? ''));
            $apellidos = trim($d['primer_apellido'].' '.($d['segundo_apellido'] ?? ''));

            $usuario = Usuario::create([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'tipo_documento' => $d['tipo_documento'],
                'numero_documento' => $d['numero_documento'],
                'correo' => $d['correo'],
                'telefono' => $d['telefono'] ?? null,
                'password' => Hash::make($d['numero_documento']),
                'id_rol' => self::ID_ROL_DOCENTE,
            ]);

            $profesor = Profesor::create([
                'id_usuario' => $usuario->id_usuario,
                'codigo_profesor' => CodigoUnicoService::generar(Profesor::class, 'codigo_profesor', 'DOC'),
                'profesion' => $d['profesion'] ?? null,
                'especialidad' => $d['especialidad'] ?? null,
                'fecha_ingreso' => $d['fecha_ingreso'] ?? now()->toDateString(),
                'estado_laboral' => 'activo',
            ]);

            $profesor->materias()->sync($d['materias'] ?? []);

            return ['id_profesor' => $profesor->id_profesor, 'codigo_profesor' => $profesor->codigo_profesor];
        });
    }

    /** No toca estado_laboral ni password — eso se maneja aparte (activar/desactivar). */
    public static function actualizar(Profesor $profesor, array $d): void
    {
        DB::transaction(function () use ($profesor, $d) {
            $nombres = trim($d['primer_nombre'].' '.($d['segundo_nombre'] ?? ''));
            $apellidos = trim($d['primer_apellido'].' '.($d['segundo_apellido'] ?? ''));

            $profesor->usuario->update([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'tipo_documento' => $d['tipo_documento'],
                'numero_documento' => $d['numero_documento'],
                'correo' => $d['correo'],
                'telefono' => $d['telefono'] ?? null,
            ]);

            $profesor->update([
                'profesion' => $d['profesion'] ?? null,
                'especialidad' => $d['especialidad'] ?? null,
                'fecha_ingreso' => $d['fecha_ingreso'] ?? $profesor->fecha_ingreso,
            ]);

            $profesor->materias()->sync($d['materias'] ?? []);
        });
    }
}
