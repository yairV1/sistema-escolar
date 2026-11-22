<?php

namespace App\Modules\Soporte\Controllers;

use App\Core\Archivos\ArchivoPrivado;
use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Soporte\Models\Soporte;
use App\Modules\Soporte\Notifications\SoporteCreado;
use App\Modules\Soporte\Notifications\SoporteEstadoActualizado;
use App\Modules\Soporte\Requests\SoporteStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SoporteController extends Controller
{
    public function create(): View
    {
        return view('Soporte.create', [
            'currentPage' => 'SoporteCreate',
        ]);
    }

    public function store(SoporteStoreRequest $request): JsonResponse
    {
        $soporte = Soporte::create([
            'id_usuario' => auth()->id(),
            'asunto' => $request->string('asunto'),
            'mensaje' => $request->string('mensaje'),
            'imagen' => $request->hasFile('imagen') ? $request->file('imagen')->store('soporte', 'private') : null,
            'estado' => 'nuevo',
        ]);

        Usuario::superAdmins()->each(fn (Usuario $admin) => $admin->notify(new SoporteCreado($soporte)));

        return response()->json([
            'success' => true,
            'message' => 'Tu soporte fue enviado. El equipo del sistema lo revisará pronto.',
        ]);
    }

    public function archivo(Request $request, Soporte $soporte, ArchivoPrivado $archivos): BinaryFileResponse
    {
        $actor = $request->user();
        abort_unless(
            $actor->esSuperAdmin()
                || ($actor->rolSlug === 'admin'
                    && (int) $actor->id_institucion === (int) $soporte->idInstitucionPropia()),
            404
        );
        abort_unless($soporte->imagen, 404);

        return $archivos->inline($soporte->imagen, 'soporte');
    }

    public function misSolicitudes(): View
    {
        $soportes = Soporte::query()
            ->where('id_usuario', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return view('Soporte.mis-solicitudes', [
            'currentPage' => 'SoporteMisSolicitudes',
            'soportes' => $soportes,
        ]);
    }

    public function index(Request $request): View
    {
        $estado = $request->query('estado');

        // SuperAdmin: mesa de ayuda de toda la plataforma. Cualquier otro rol
        // con acceso (admin institucional): solo los soportes de su institución.
        $acotar = fn ($q) => auth()->user()->esSuperAdmin() ? $q : $q->deInstitucion();

        $soportes = $acotar(Soporte::query())
            ->with('remitente')
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->orderByDesc('created_at')
            ->get();

        return view('Soporte.index', [
            'currentPage' => 'SoportesIndex',
            'soportes' => $soportes,
            'estadoSeleccionado' => $estado,
            'kpis' => [
                'nuevos' => $acotar(Soporte::query())->where('estado', 'nuevo')->count(),
                'leidos' => $acotar(Soporte::query())->where('estado', 'leido')->count(),
                'resueltos' => $acotar(Soporte::query())->where('estado', 'resuelto')->count(),
            ],
        ]);
    }

    public function show(Soporte $soporte): View
    {
        if ($soporte->estado === 'nuevo') {
            $soporte->update(['estado' => 'leido']);
            $soporte->remitente?->notify(new SoporteEstadoActualizado($soporte));
        }

        return view('Soporte.show', [
            'currentPage' => 'SoportesIndex',
            'soporte' => $soporte->load('remitente', 'resueltoPor'),
        ]);
    }

    public function resolver(Soporte $soporte): JsonResponse
    {
        $soporte->update([
            'estado' => 'resuelto',
            'resuelto_por' => auth()->id(),
            'resuelto_at' => now(),
        ]);

        $soporte->remitente?->notify(new SoporteEstadoActualizado($soporte));

        return response()->json([
            'success' => true,
            'message' => 'Soporte marcado como resuelto.',
        ]);
    }
}
