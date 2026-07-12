<?php

namespace App\Http\Controllers;

use App\Http\Requests\Registro\DocenteStoreRequest;
use App\Models\Profesor;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RegistroDocentesController extends Controller
{
    public function create(): View
    {
        return view('registro.docentes', ['currentPage' => 'RegistroDocentes']);
    }

    public function store(DocenteStoreRequest $request): JsonResponse
    {
        try {
            $resultado = Profesor::crearCompleto($request->validated());
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un usuario con ese documento o correo.',
                ], 409);
            }

            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Docente registrado correctamente.',
            'codigo' => $resultado['codigo_profesor'],
        ]);
    }
}
