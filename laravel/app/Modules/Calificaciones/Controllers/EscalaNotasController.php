<?php

namespace App\Modules\Calificaciones\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calificaciones\Models\EscalaNota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EscalaNotasController extends Controller
{
    /** Reemplaza la escala completa en una sola operación: son pocas filas (Bajo/Básico/Alto/Superior)
     *  que se editan en bloque, no un CRUD paginado como periodos o tipos de actividad. */
    public function guardar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'escalas' => ['required', 'array', 'min:1'],
            'escalas.*.id_escala' => ['nullable', 'integer', 'exists:escala_notas,id_escala'],
            'escalas.*.etiqueta' => ['required', 'string', 'max:50'],
            'escalas.*.sigla' => ['required', 'string', 'max:5'],
            'escalas.*.valor_min' => ['required', 'numeric', 'min:0', 'max:5'],
            'escalas.*.valor_max' => ['required', 'numeric', 'min:0', 'max:5', 'gte:escalas.*.valor_min'],
            'escalas.*.orden' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $idsConservados = [];

            foreach ($data['escalas'] as $fila) {
                $valores = [
                    'etiqueta' => $fila['etiqueta'],
                    'sigla' => $fila['sigla'],
                    'valor_min' => $fila['valor_min'],
                    'valor_max' => $fila['valor_max'],
                    'orden' => $fila['orden'],
                ];

                $escala = empty($fila['id_escala'])
                    ? EscalaNota::create($valores)
                    : tap(EscalaNota::findOrFail($fila['id_escala']))->update($valores);

                $idsConservados[] = $escala->id_escala;
            }

            EscalaNota::whereNotIn('id_escala', $idsConservados)->delete();
        });

        return response()->json(['success' => true, 'message' => 'Escala de notas actualizada correctamente.']);
    }
}
