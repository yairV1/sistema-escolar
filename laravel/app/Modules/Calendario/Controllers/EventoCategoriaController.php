<?php

namespace App\Modules\Calendario\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Models\EventoCategoria;
use App\Modules\Calendario\Requests\EventoCategoriaRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventoCategoriaController extends Controller
{
    public function index(): View
    {
        return view('Rector.calendario.categorias.index', [
            'currentPage' => 'CalendarioCategorias',
            'categorias' => EventoCategoria::orderBy('orden')->orderBy('nombre')->get(),
            'coloresDisponibles' => EventoCategoriaRequest::COLORES_VALIDOS,
            'rolesDisponibles' => Usuario::allRoleSlugs(),
        ]);
    }

    public function store(EventoCategoriaRequest $request): JsonResponse
    {
        $datos = $request->validated();
        $datos['slug'] = $this->generarSlugUnico($datos['nombre']);
        $datos['estado'] = 'activo';
        $datos['es_sistema'] = false;

        try {
            $categoria = EventoCategoria::create($datos);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe una categoría con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Categoría creada correctamente.', 'id' => $categoria->id_categoria]);
    }

    public function update(EventoCategoriaRequest $request, EventoCategoria $categoria): JsonResponse
    {
        $datos = $request->validated();

        // Categorías del sistema (Clases, Actividades) no se crean/editan por CRUD:
        // sus permisos de rol no tienen sentido y se descartan aunque lleguen en el POST.
        if ($categoria->es_sistema) {
            $datos = Arr::except($datos, ['roles_crear', 'roles_editar', 'roles_eliminar']);
        }

        try {
            $categoria->update($datos);
        } catch (QueryException $e) {
            return $this->respuestaDuplicado($e, 'Ya existe una categoría con ese nombre.');
        }

        return response()->json(['success' => true, 'message' => 'Categoría actualizada correctamente.']);
    }

    public function desactivar(EventoCategoria $categoria): JsonResponse
    {
        if ($categoria->es_sistema) {
            return response()->json(['success' => false, 'message' => 'Las categorías del sistema no se pueden desactivar.'], 422);
        }

        $categoria->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Categoría desactivada correctamente.']);
    }

    public function activar(EventoCategoria $categoria): JsonResponse
    {
        $categoria->update(['estado' => 'activo']);

        return response()->json(['success' => true, 'message' => 'Categoría reactivada correctamente.']);
    }

    private function generarSlugUnico(string $nombre): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $sufijo = 1;

        while (EventoCategoria::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$sufijo);
        }

        return $slug;
    }

    private function respuestaDuplicado(QueryException $e, string $mensaje): JsonResponse
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json(['success' => false, 'message' => $mensaje], 409);
        }

        throw $e;
    }
}
