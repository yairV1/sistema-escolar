<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Modulos\Models\Modulo;
use App\Modules\Modulos\Requests\ModuloStoreRequest;
use App\Modules\Modulos\Requests\ModuloUpdateRequest;
use App\Modules\Planes\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuloController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** Requiere plataforma.modulos.ver. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Modulo::class);

        $modulos = Modulo::query()
            ->withCount('planes')
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->query('categoria')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->query('estado')))
            ->orderBy('categoria')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('SuperAdmin.modulos.index', [
            'currentPage' => 'SuperAdminModulos',
            'modulos' => $modulos,
            'filtros' => $request->only(['categoria', 'estado']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Modulo::class);

        return view('SuperAdmin.modulos.create', [
            'currentPage' => 'SuperAdminModulos',
            'planes' => Plan::where('estado', 'activo')->orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }

    public function store(ModuloStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Modulo::class);

        $modulo = Modulo::create($request->safe()->except('planes'));
        $modulo->planes()->sync($request->input('planes', []));

        $this->auditLogger->record('modulo.crear', $modulo, [], $modulo->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Módulo creado correctamente.',
            'redirect' => route('superadmin.modulos.index'),
        ]);
    }

    public function edit(Modulo $modulo): View
    {
        $this->authorize('update', $modulo);

        $modulo->load('planes');

        return view('SuperAdmin.modulos.edit', [
            'currentPage' => 'SuperAdminModulos',
            'modulo' => $modulo,
            'planes' => Plan::where('estado', 'activo')->orderBy('orden')->orderBy('nombre')->get(),
            'planesSeleccionados' => $modulo->planes->pluck('id_plan')->all(),
        ]);
    }

    public function update(ModuloUpdateRequest $request, Modulo $modulo): JsonResponse
    {
        $this->authorize('update', $modulo);

        $antes = $modulo->toArray();
        $modulo->update($request->safe()->except('planes'));
        $modulo->planes()->sync($request->input('planes', []));

        $this->auditLogger->record('modulo.editar', $modulo, $antes, $modulo->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Módulo actualizado correctamente.',
            'redirect' => route('superadmin.modulos.index'),
        ]);
    }

    public function activar(Modulo $modulo): JsonResponse
    {
        $this->authorize('activate', $modulo);

        $modulo->update(['estado' => 'activo']);
        $this->auditLogger->record('modulo.activar', $modulo);

        return response()->json(['success' => true, 'message' => 'Módulo activado.']);
    }

    public function desactivar(Modulo $modulo): JsonResponse
    {
        $this->authorize('deactivate', $modulo);

        $modulo->update(['estado' => 'inactivo']);
        $this->auditLogger->record('modulo.desactivar', $modulo);

        return response()->json(['success' => true, 'message' => 'Módulo desactivado.']);
    }
}
