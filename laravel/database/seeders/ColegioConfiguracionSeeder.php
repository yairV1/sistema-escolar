<?php

namespace Database\Seeders;

use App\Modules\Colegio\Models\ColegioConfiguracion;
use App\Modules\Colegio\Models\ColegioImagen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Datos de ejemplo para poder probar la vista de Configuración del Colegio
 * sin llenarla a mano. El logo y las fotos de galería son SVG generados en
 * memoria (no hay assets institucionales reales en el repo ni extensión GD
 * disponible en todos los entornos), así que sirven solo de placeholder.
 */
class ColegioConfiguracionSeeder extends Seeder
{
    private const GALERIA = [
        'fachada' => ['#2d7a4f', 'Fachada principal'],
        'patio' => ['#c8a84b', 'Patio de descanso'],
        'aulas' => ['#1e5436', 'Sala de sistemas'],
        'deportes' => ['#4a9e6b', 'Cancha múltiple'],
    ];

    public function run(): void
    {
        $configuracion = ColegioConfiguracion::singleton();

        $configuracion->update([
            'nombre_colegio' => 'Colegio San Cristóbal',
            'logo' => $this->placeholder('colegio/logo.svg', 'SC', '#2d7a4f', 240, 240),
            'direccion' => 'Cra. 45 # 12-30, Barrio Los Alpes',
            'ciudad' => 'Bucaramanga',
            'departamento' => 'Santander',
            'pais' => 'Colombia',
            'telefono' => '607 642 1100',
            'email_institucional' => 'contacto@sancristobal.edu.co',
            'sitio_web' => 'https://sancristobal.edu.co',
            'descripcion' => 'Formamos bachilleres íntegros, con pensamiento crítico y vocación de servicio, comprometidos con su comunidad y su país.',
        ]);

        if ($configuracion->imagenes()->count() > 0) {
            return;
        }

        $orden = 1;
        foreach (self::GALERIA as $tipo => [$color, $descripcion]) {
            $configuracion->imagenes()->create([
                'imagen' => $this->placeholder("colegio/galeria/{$tipo}.svg", ColegioImagen::TIPOS_LABELS[$tipo], $color, 640, 400),
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'orden' => $orden++,
            ]);
        }
    }

    private function placeholder(string $path, string $label, string $color, int $width, int $height): string
    {
        if (! Storage::disk('public')->exists($path)) {
            $svg = <<<SVG
                <svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
                    <rect width="100%" height="100%" fill="{$color}"/>
                    <text x="50%" y="50%" fill="#ffffff" font-family="Arial, sans-serif" font-size="28" font-weight="bold" text-anchor="middle" dominant-baseline="middle">{$label}</text>
                </svg>
                SVG;

            Storage::disk('public')->put($path, $svg);
        }

        return $path;
    }
}
