<?php

namespace App\Modules\Landing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\Instituciones\Models\Institucion;
use App\Modules\Landing\Support\SitioInstitucional;
use Illuminate\View\View;

/**
 * Sitio público — Etapa 2 · B7. Ver SitioInstitucional.
 *  /                    → sitio de PORTAL_PUBLICO_SLUG (validado) o página neutral
 *  /sitio/{slug}        → sitio de esa institución (404 si no existe o no está activa)
 *  /sitio/{slug}/ingresar → login con la marca de esa institución
 */
class WebsiteController extends Controller
{
    public function index(): View
    {
        $portal = SitioInstitucional::portal();

        return $portal ? $this->render($portal) : view('website.neutral', ['colegioConfiguracion' => ColegioConfiguracion::neutra()]);
    }

    public function sitio(string $slug): View
    {
        return $this->render(SitioInstitucional::porSlug($slug));
    }

    public function ingresar(string $slug): View
    {
        $inst = SitioInstitucional::porSlug($slug);

        return view('auth.login', ['colegioConfiguracion' => ColegioConfiguracion::deInstitucion($inst->id_institucion)]);
    }

    private function render(Institucion $inst): View
    {
        return view('website.index', [
            'institucionSitio' => $inst,
            'colegioConfiguracion' => ColegioConfiguracion::deInstitucion($inst->id_institucion),
            'contenido' => SitioInstitucional::contenido($inst),
            'noticias' => SitioInstitucional::noticias($inst),
            'galeria' => SitioInstitucional::galeria($inst),
            'grados' => SitioInstitucional::grados($inst),
            'urlAdmision' => route('sitio.admision', $inst->slug),
            'urlIngresar' => route('sitio.ingresar', $inst->slug),
        ]);
    }
}
