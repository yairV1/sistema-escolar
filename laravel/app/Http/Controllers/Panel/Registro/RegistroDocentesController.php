<?php

namespace App\Http\Controllers\Panel\Registro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registro\DocenteStoreRequest;
use App\Http\Requests\Registro\DocenteUpdateRequest;
use App\Models\Profesor;
use App\Services\DocenteRegistroService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RegistroDocentesController extends Controller
{
    public function create(): View
    {
        return view('panel.registro.docentes', ['currentPage' => 'RegistroDocentes', 'profesor' => null]);
    }

    public function store(DocenteStoreRequest $request): JsonResponse
    {
        try {
            $resultado = DocenteRegistroService::crear($request->validated());
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

    public function edit(Profesor $profesor): View
    {
        $profesor->load('usuario');

        return view('panel.registro.docentes', ['currentPage' => 'RegistroDocentes', 'profesor' => $profesor]);
    }

    public function update(DocenteUpdateRequest $request, Profesor $profesor): JsonResponse
    {
        try {
            DocenteRegistroService::actualizar($profesor, $request->validated());
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
            'message' => 'Docente actualizado correctamente.',
            'codigo' => $profesor->codigo_profesor,
        ]);
    }
}
