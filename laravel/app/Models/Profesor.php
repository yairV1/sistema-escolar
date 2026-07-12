<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Profesor extends Model
{
    protected $table = 'profesores';

    protected $primaryKey = 'id_profesor';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'codigo_profesor',
        'profesion',
        'especialidad',
        'fecha_ingreso',
        'estado_laboral',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionAcademica::class, 'id_profesor', 'id_profesor');
    }

    private const ID_ROL_DOCENTE = 5;

    public static function generarCodigoUnico(): string
    {
        do {
            $codigo = sprintf('%s-DOC-%04d', date('Y'), static::count() + 1);
        } while (static::where('codigo_profesor', $codigo)->exists());

        return $codigo;
    }

    /**
     * @return array{id_profesor:int,codigo_profesor:string}
     */
    public static function crearCompleto(array $d): array
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

            $profesor = static::create([
                'id_usuario' => $usuario->id_usuario,
                'codigo_profesor' => static::generarCodigoUnico(),
                'profesion' => $d['profesion'] ?? null,
                'especialidad' => $d['especialidad'] ?? null,
                'fecha_ingreso' => $d['fecha_ingreso'] ?? now()->toDateString(),
                'estado_laboral' => 'activo',
            ]);

            return ['id_profesor' => $profesor->id_profesor, 'codigo_profesor' => $profesor->codigo_profesor];
        });
    }
}
