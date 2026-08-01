<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Modulos\Models\Modulo;
use App\Modules\Modulos\Requests\ModuloStoreRequest;
use App\Modules\Modulos\Requests\ModuloUpdateRequest;
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

        return view('SuperAdmin.modulos.create', ['currentPage' => 'SuperAdminModulos']);
    }

    public function store(ModuloStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Modulo::class);

        $modulo = Modulo::create($request->validated());

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

        return view('SuperAdmin.modulos.edit', [
            'currentPage' => 'SuperAdminModulos',
            'modulo' => $modulo,
        ]);
    }

    public function update(ModuloUpdateRequest $request, Modulo $modulo): JsonResponse
    {
        $this->authorize('update', $modulo);

        $antes = $modulo->toArray();
        $modulo->update($request->validated());

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
