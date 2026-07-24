<?php

namespace App\Modules\Calendario\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Models\EventoCategoria;
use App\Modules\Calendario\Requests\CalendarioFeedRequest;
use App\Modules\Calendario\Services\CalendarioFeedService;
use App\Modules\Calendario\Services\VisibilidadCalendarioService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarioController extends Controller
{
    public function __construct(private VisibilidadCalendarioService $visibilidad) {}

    public function index(Request $request): View
    {
        $categorias = EventoCategoria::query()->activas()->orderBy('orden')->get();
        $usuario = $request->user();

        return view('Rector.calendario.index', [
            'currentPage' => 'Calendario',
            'categorias' => $categorias,
            'categoriasCreables' => $categorias->filter(fn (EventoCategoria $categoria) => $usuario->can('create', [Evento::class, $categoria]))->values(),
            'puedeGestionarCategorias' => $usuario->tienePanelAdmin(),
            'cursosParaFiltro' => $this->visibilidad->cursosParaFiltro($usuario),
            'personalParaCompartir' => $this->visibilidad->personalParaCompartir(),
        ]);
    }

    public function feed(CalendarioFeedRequest $request, CalendarioFeedService $calendarioFeedService): JsonResponse
    {
        $desde = CarbonImmutable::parse($request->input('desde'));
        $hasta = CarbonImmutable::parse($request->input('hasta'));

        $items = $calendarioFeedService->feed($desde, $hasta, $request->user(), $request->filtros());

        return response()->json($items);
    }
}
