<?php

namespace App\Modules\Landing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Landing\Models\LandingContenido;
use App\Modules\Landing\Models\LandingGaleria;
use App\Modules\Landing\Models\LandingNoticia;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(): View
    {
        return view('website.index', [
            'contenido' => LandingContenido::pluck('valor', 'clave'),
            'noticias' => LandingNoticia::where('estado', 'activo')->orderBy('orden')->get(),
            'galeria' => LandingGaleria::where('estado', 'activo')->orderBy('orden')->get(),
        ]);
    }
}
