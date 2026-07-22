<?php

namespace App\Modules\Usuarios\Controllers\Registro;

use App\Core\Http\Controllers\Controller;
use App\Modules\Matriculas\Models\SolicitudAdmision;
use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Requests\Registro\EstudianteStoreRequest;
use App\Modules\Usuarios\Requests\Registro\EstudianteUpdateRequest;
use App\Modules\Usuarios\Services\EstudianteRegistroService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistroEstudiantesController extends Controller
{
    public function create(Request $request): View
    {
        $solicitud = null;
        if ($idSolicitud = $request->query('solicitud')) {
            $solicitud = SolicitudAdmision::where('estado', '!=', 'convertida')->find($idSolicitud);
        }

        return view('Rector.usuarios.registro.estudiantes', [
            'currentPage' => 'RegistroEstudiantes',
            'estudiante' => null,
            'matricula' => null,
            'grado' => '',
            'grupo' => '',
            'acudiente' => null,
            'solicitud' => $solicitud,
        ]);
    }

    public function store(EstudianteStoreRequest $request): JsonResponse
    {
        try {
            $resultado = EstudianteRegistroService::crear($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estudiante registrado correctamente.',
            'codigo' => $resultado['codigo_estudiante'],
        ]);
    }

    public function edit(Estudiante $estudiante): View
    {
        $estudiante->load(['usuario', 'matriculas' => fn ($q) => $q->latest('anio_lectivo')->with('curso')]);

        $matricula = $estudiante->matriculas->first();
        $nombreCurso = $matricula?->curso?->nombre_curso ?? '';
        $grado = rtrim($nombreCurso, 'ABCD');
        $grupo = substr($nombreCurso, strlen($grado));

        $acudiente = DB::table('estudiante_acudiente')
            ->join('acudientes', 'acudientes.id_acudiente', '=', 'estudiante_acudiente.id_acudiente')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'acudientes.id_usuario')
            ->where('estudiante_acudiente.id_estudiante', $estudiante->id_estudiante)
            ->where('estudiante_acudiente.es_principal', 1)
            ->select('usuarios.nombres', 'usuarios.apellidos', 'usuarios.tipo_documento', 'usuarios.numero_documento', 'usuarios.correo', 'usuarios.telefono', 'acudientes.ocupacion', 'estudiante_acudiente.parentesco')
            ->first();

        return view('Rector.usuarios.registro.estudiantes', [
            'currentPage' => 'RegistroEstudiantes',
            'estudiante' => $estudiante,
            'matricula' => $matricula,
            'grado' => $grado,
            'grupo' => $grupo,
            'acudiente' => $acudiente,
            'solicitud' => null,
        ]);
    }

    public function update(EstudianteUpdateRequest $request, Estudiante $estudiante): JsonResponse
    {
        try {
            EstudianteRegistroService::actualizar($estudiante, $request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estudiante actualizado correctamente.',
            'codigo' => $estudiante->codigo_estudiante,
        ]);
    }

    private function respuestaDuplicado(QueryException $e): JsonResponse
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un usuario con ese documento o correo.',
            ], 409);
        }

        throw $e;
    }
}
