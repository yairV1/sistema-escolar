<?php

namespace App\Modules\Comunicados\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Comunicados\Models\Notificacion;
use App\Modules\Comunicados\Requests\ComunicadoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ComunicadosController extends Controller
{
    /** Mapeo de audiencia elegida en el form -> ids de rol reales de la BD. */
    private const AUDIENCIAS = [
        'estudiantes' => [6],
        'docentes' => [5],
        'administrativos' => [1, 2, 3, 4],
        'acudientes' => [7],
    ];

    public function index(): View
    {
        $comunicados = DB::table('notificaciones')
            ->select(
                'titulo',
                'mensaje',
                'tipo_notificacion',
                'canal',
                'fecha_envio',
                DB::raw('COUNT(*) as total_destinatarios'),
                DB::raw('SUM(leida) as total_leidos')
            )
            ->groupBy('titulo', 'mensaje', 'tipo_notificacion', 'canal', 'fecha_envio')
            ->orderByDesc('fecha_envio')
            ->paginate(15);

        return view('Rector.comunicados.index', [
            'currentPage' => 'Comunicados',
            'comunicados' => $comunicados,
        ]);
    }

    public function store(ComunicadoRequest $request): JsonResponse
    {
        $data = $request->validated();

        $idsRoles = collect($data['audiencias'])->flatMap(fn ($a) => self::AUDIENCIAS[$a])->unique()->values();

        $destinatarios = Usuario::where('estado_usuario', 'activo')
            ->whereIn('id_rol', $idsRoles)
            ->pluck('id_usuario');

        if ($destinatarios->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay usuarios activos para los destinatarios seleccionados.'], 422);
        }

        $ahora = now();
        $filas = $destinatarios->map(fn ($id) => [
            'titulo' => $data['titulo'],
            'mensaje' => $data['mensaje'],
            'id_usuario_origen' => auth()->id(),
            'id_usuario_destino' => $id,
            'tipo_notificacion' => $data['tipo_notificacion'],
            'canal' => $data['canal'],
            'fecha_envio' => $ahora,
            'leida' => false,
            'estado' => 'enviada',
        ])->all();

        Notificacion::insert($filas);

        return response()->json(['success' => true, 'message' => 'Comunicado enviado a '.count($filas).' destinatario(s).']);
    }
}
