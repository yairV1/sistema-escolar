<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Instituciones\Requests\InstitucionStoreRequest;
use App\Modules\Instituciones\Requests\InstitucionUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitucionController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** Requiere plataforma.instituciones.ver. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Institucion::class);

        $instituciones = Institucion::query()
            ->withCount('usuarios')
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->query('buscar').'%'))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->query('estado')))
            ->when($request->filled('plan'), fn ($q) => $q->where('plan', $request->query('plan')))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('SuperAdmin.instituciones.index', [
            'currentPage' => 'SuperAdminInstituciones',
            'instituciones' => $instituciones,
            'filtros' => $request->only(['buscar', 'estado', 'plan']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Institucion::class);

        return view('SuperAdmin.instituciones.create', ['currentPage' => 'SuperAdminInstituciones']);
    }

    public function store(InstitucionStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Institucion::class);

        $institucion = Institucion::create([...$request->validated(), 'estado' => 'activa']);

        $this->auditLogger->record('institucion.crear', $institucion, [], $institucion->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Institución creada correctamente.',
            'redirect' => route('superadmin.instituciones.show', $institucion),
        ]);
    }

    /** Pestañas por query string (?tab=info|usuarios|metricas|configuracion), mismo patrón que Soporte/index.blade.php. */
    public function show(Institucion $institucion, Request $request): View
    {
        $this->authorize('view', $institucion);

        $institucion->loadCount('usuarios');

        return view('SuperAdmin.instituciones.show', [
            'currentPage' => 'SuperAdminInstituciones',
            'institucion' => $institucion,
            'tab' => $request->query('tab', 'info'),
            'usuarios' => $institucion->usuarios()->with('rol')->paginate(15, ['*'], 'usuarios_page'),
        ]);
    }

    public function edit(Institucion $institucion): View
    {
        $this->authorize('update', $institucion);

        return view('SuperAdmin.instituciones.edit', [
            'currentPage' => 'SuperAdminInstituciones',
            'institucion' => $institucion,
        ]);
    }

    public function update(InstitucionUpdateRequest $request, Institucion $institucion): JsonResponse
    {
        $this->authorize('update', $institucion);

        $antes = $institucion->toArray();
        $institucion->update($request->validated());

        $this->auditLogger->record('institucion.editar', $institucion, $antes, $institucion->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Institución actualizada correctamente.',
            'redirect' => route('superadmin.instituciones.show', $institucion),
        ]);
    }

    public function activar(Institucion $institucion): JsonResponse
    {
        $this->authorize('activate', $institucion);

        $institucion->update(['estado' => 'activa']);
        $this->auditLogger->record('institucion.activar', $institucion);

        return response()->json(['success' => true, 'message' => 'Institución activada.']);
    }

    public function desactivar(Institucion $institucion): JsonResponse
    {
        $this->authorize('deactivate', $institucion);

        $institucion->update(['estado' => 'suspendida']);
        $this->auditLogger->record('institucion.suspender', $institucion);

        return response()->json(['success' => true, 'message' => 'Institución suspendida.']);
    }
}
