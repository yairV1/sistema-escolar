<?php

namespace App\Core\Archivos;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ArchivoPrivado
{
    public function inline(string $ruta, string $directorio): BinaryFileResponse
    {
        $prefijo = trim($directorio, '/').'/';
        $segmentos = explode('/', $ruta);

        abort_unless(
            str_starts_with($ruta, $prefijo)
                && ! str_contains($ruta, '\\')
                && ! in_array('.', $segmentos, true)
                && ! in_array('..', $segmentos, true),
            404
        );

        foreach (['private', 'public'] as $nombreDisco) {
            $disco = Storage::disk($nombreDisco);
            if ($disco->exists($ruta)) {
                $respuesta = response()->file($disco->path($ruta), [
                    'X-Content-Type-Options' => 'nosniff',
                    'Referrer-Policy' => 'no-referrer',
                ]);
                $respuesta->setPrivate();
                $respuesta->headers->set('Cache-Control', 'private, no-store');

                return $respuesta;
            }
        }

        abort(404);
    }
}
