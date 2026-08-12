<?php

namespace App\Modules\Busqueda\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\GestionAcademica\Models\Curso;
use App\Modules\Usuarios\Models\Estudiante;
use App\Modules\Usuarios\Models\Profesor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Buscador de la barra superior del panel (rector/admin) — mismo filtrado por institución que el resto del panel. */
class BusquedaController extends Controller
{
    private const LIMITE_POR_GRUPO = 5;

    public function index(Request $request): JsonResponse
    {
        $termino = trim((string) $request->query('q', ''));

        if (mb_strlen($termino) < 2) {
            return response()->json(['estudiantes' => [], 'docentes' => [], 'cursos' => []]);
        }

        $idInstitucion = auth()->user()->id_institucion;
        $like = '%'.$termino.'%';

        $estudiantes = Estudiante::with('usuario')
            ->whereHas('usuario', function ($query) use ($idInstitucion, $like) {
                $query->where('id_institucion', $idInstitucion)
                    ->where(function ($sub) use ($like) {
                        $sub->where('nombres', 'like', $like)
                            ->orWhere('apellidos', 'like', $like)
                            ->orWhere('numero_documento', 'like', $like);
                    });
            })
            ->limit(self::LIMITE_POR_GRUPO)
            ->get()
            ->map(fn (Estudiante $estudiante) => [
                'titulo' => trim($estudiante->usuario->nombres.' '.$estudiante->usuario->apellidos),
                'subtitulo' => $estudiante->usuario->numero_documento ?? 'Estudiante',
                'url' => route('registro.estudiantes.show', $estudiante),
            ]);

        $docentes = Profesor::with('usuario')
            ->whereHas('usuario', function ($query) use ($idInstitucion, $like) {
                $query->where('id_institucion', $idInstitucion)
                    ->where(function ($sub) use ($like) {
                        $sub->where('nombres', 'like', $like)
                            ->orWhere('apellidos', 'like', $like);
                    });
            })
            ->limit(self::LIMITE_POR_GRUPO)
            ->get()
            ->map(fn (Profesor $profesor) => [
                'titulo' => trim($profesor->usuario->nombres.' '.$profesor->usuario->apellidos),
                'subtitulo' => $profesor->especialidad ?: 'Docente',
                'url' => route('registro.docentes.show', $profesor),
            ]);

        $cursos = Curso::where('id_institucion', $idInstitucion)
            ->where('nombre_curso', 'like', $like)
            ->limit(self::LIMITE_POR_GRUPO)
            ->get()
            ->map(fn (Curso $curso) => [
                'titulo' => $curso->nombre_curso,
                'subtitulo' => $curso->nivel_academico ? ucfirst($curso->nivel_academico) : 'Curso',
                'url' => route('gestion-academica.cursos.show', $curso),
            ]);

        return response()->json([
            'estudiantes' => $estudiantes,
            'docentes' => $docentes,
            'cursos' => $cursos,
        ]);
    }
}
