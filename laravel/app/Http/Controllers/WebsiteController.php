<?php

namespace App\Http\Controllers;

use App\Models\LandingContenido;
use App\Models\LandingGaleria;
use App\Models\LandingNoticia;
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
