<?php

namespace App\Modules\GestionAcademica\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\GestionAcademica\Models\Area;
use App\Modules\GestionAcademica\Models\Materia;
use App\Modules\GestionAcademica\Requests\AreaRequest;
use App\Modules\GestionAcademica\Requests\MateriaRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MateriasController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'materias');
        $tab = in_array($tab, ['materias', 'areas'], true) ? $tab : 'materias';

        return view('Rector.gestion-academica.materias.index', [
            'currentPage' => 'GestionAcademicaMaterias',
            'tab' => $tab,
            'filtros' => $request->only(['q', 'estado']),
            'todasLasAreas' => $tab === 'materias' ? Area::where('estado', 'activo')->orderBy('nombre_area')->get() : null,
            'materias' => $tab === 'materias' ? $this->buscarMaterias($request) : null,
            'resumenMaterias' => $tab === 'materias' ? $this->resumenMaterias() : null,
            'areas' => $tab === 'areas' ? $this->buscarAreas($request) : null,
            'resumenAreas' => $tab === 'areas' ? $this->resumenAreas() : null,
        ]);
    }

    private function buscarMaterias(Request $request)
    {
        $query = Materia::query()->with('area');

        if ($q = $request->query('q')) {
            $query->where('nombre_materia', 'like', "%{$q}%");
        }

        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('nombre_materia')->paginate(15)->withQueryString();
    }

    private function resumenMaterias(): array
    {
        return [
            'total' => Materia::count(),
            'activas' => Materia::where('estado', 'activo')->count(),
            'inactivas' => Materia::where('estado', 'inactivo')->count(),
        ];
    }

    private function buscarAreas(Request $request)
    {
        $query = Area::query();

        if ($q = $request->query('q')) {
            $query->where('nombre_area', 'like', "%{$q}%");
        }

        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('nombre_area')->paginate(15)->withQueryString();
    }

    private function resumenAreas(): array
    {
        return [
            'total' => Area::count(),
            'activas' => Area::where('estado', 'activo')->count(),
            'inactivas' => Area::where('estado', 'inactivo')->count(),
        ];
    }

    public function store(MateriaRequest $request): JsonResponse
    {
        try {
            $materia = Materia::create($request->validated() + ['estado' => 'activo']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe una materia con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Materia creada correctamente.', 'id' => $materia->id_materia]);
    }

    public function update(MateriaRequest $request, Materia $materia): JsonResponse
    {
        try {
            $materia->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe una materia con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Materia actualizada correctamente.']);
    }

    public function desactivar(Materia $materia): JsonResponse
    {
        $materia->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Materia desactivada correctamente.']);
    }

    public function activar(Materia $materia): JsonResponse
    {
        $materia->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Materia reactivada correctamente.']);
    }

    public function storeArea(AreaRequest $request): JsonResponse
    {
        try {
            $area = Area::create($request->validated() + ['estado' => 'activo']);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un área con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Área creada correctamente.', 'id' => $area->id_area]);
    }

    public function updateArea(AreaRequest $request, Area $area): JsonResponse
    {
        try {
            $area->update($request->validated());
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe un área con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Área actualizada correctamente.']);
    }

    public function desactivarArea(Area $area): JsonResponse
    {
        $area->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Área desactivada correctamente.']);
    }

    public function activarArea(Area $area): JsonResponse
    {
        $area->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Área reactivada correctamente.']);
    }

    private function respuestaDuplicado(QueryException $e, string $mensaje): JsonResponse
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json(['success' => false, 'message' => $mensaje], 409);
        }

        throw $e;
    }
}
