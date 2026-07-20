<?php

namespace App\Modules\Usuarios\Models;

use App\Modules\Auth\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Port de app/models/Acudiente.php (legacy): buscarOCrear() es idempotente
 * por número de documento (si el usuario ya existe y ya tiene fila en
 * `acudientes`, se reusa tal cual — permite que un mismo acudiente
 * quede vinculado a varios estudiantes).
 */
class Acudiente extends Model
{
    protected $table = 'acudientes';

    protected $primaryKey = 'id_acudiente';

    public $timestamps = false;

    private const ID_ROL_ACUDIENTE = 7;

    protected $fillable = [
        'id_usuario',
        'ocupacion',
        'empresa',
        'estado',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * @param  array{nombres:string,apellidos:string,tipo_documento:string,numero_documento:string,correo:?string,telefono:?string,ocupacion:?string}  $datos
     */
    public static function buscarOCrear(array $datos): int
    {
        $usuario = Usuario::where('numero_documento', $datos['numero_documento'])->first();

        if ($usuario) {
            $existente = static::where('id_usuario', $usuario->id_usuario)->first();
            if ($existente) {
                return $existente->id_acudiente;
            }
        } else {
            $usuario = Usuario::create([
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'tipo_documento' => $datos['tipo_documento'],
                'numero_documento' => $datos['numero_documento'],
                'correo' => $datos['correo'] ?? ($datos['numero_documento'].'@acudientes.sancristobal.edu.co'),
                'telefono' => $datos['telefono'] ?? null,
                'password' => Hash::make($datos['numero_documento']),
                'id_rol' => self::ID_ROL_ACUDIENTE,
            ]);
        }

        $acudiente = static::create([
            'id_usuario' => $usuario->id_usuario,
            'ocupacion' => $datos['ocupacion'] ?? null,
            'estado' => 'activo',
        ]);

        return $acudiente->id_acudiente;
    }

    public static function vincularEstudiante(int $idEstudiante, int $idAcudiente, string $parentesco, bool $esPrincipal): void
    {
        $existe = DB::table('estudiante_acudiente')
            ->where('id_estudiante', $idEstudiante)
            ->where('id_acudiente', $idAcudiente)
            ->exists();

        if ($existe) {
            return;
        }

        DB::table('estudiante_acudiente')->insert([
            'id_estudiante' => $idEstudiante,
            'id_acudiente' => $idAcudiente,
            'parentesco' => $parentesco,
            'es_principal' => $esPrincipal,
            'estado' => 'activo',
            'fecha_registro' => now(),
        ]);
    }
}
