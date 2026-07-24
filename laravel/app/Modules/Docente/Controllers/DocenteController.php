<?php

namespace App\Modules\Docente\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use Illuminate\View\View;

class DocenteController extends Controller
{
    public function dashboard(): View
    {
        $profesor = auth()->user()->profesor;

        abort_if(! $profesor, 404, 'Tu usuario no tiene un perfil de profesor asociado. Contacta al colegio.');

        $asignaciones = AsignacionAcademica::where('id_profesor', $profesor->id_profesor)
            ->where('estado', 'activo')
            ->with(['materia', 'curso'])
            ->get()
            ->sortBy(fn ($asignacion) => $asignacion->curso->nombre_curso.$asignacion->materia->nombre_materia);

        return view('Docente.dashboard', [
            'currentPage' => 'DocenteDashboard',
            'asignaciones' => $asignaciones,
        ]);
    }
}
