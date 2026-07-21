<?php

namespace App\Modules\Comunicados\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envio de comunicados via WhatsApp Cloud API (Meta), usando un mensaje
 * de plantilla porque el colegio inicia la conversacion (fuera de la
 * ventana de 24h de servicio al cliente, Meta exige plantilla aprobada
 * para mensajes iniciados por el negocio).
 */
class WhatsappCloudService
{
    public function enviarComunicado(string $telefono, string $titulo, string $mensaje): bool
    {
        $config = config('services.whatsapp_cloud');

        $numero = $this->normalizarTelefono($telefono);
        if ($numero === null) {
            Log::warning('WhatsApp: telefono invalido, no se envio comunicado.', ['telefono' => $telefono]);

            return false;
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $config['api_version'],
            $config['phone_number_id'],
        );

        $response = Http::withToken($config['token'])
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $numero,
                'type' => 'template',
                'template' => [
                    'name' => $config['template_name'],
                    'language' => ['code' => $config['template_lang']],
                    'components' => [
                        [
                            'type' => 'header',
                            'parameters' => [
                                ['type' => 'text', 'text' => $titulo],
                            ],
                        ],
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $mensaje],
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::warning('WhatsApp: fallo el envio del comunicado.', [
                'telefono' => $numero,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Numeros en la BD estan guardados en formato local colombiano (10
     * digitos, a veces con espacios). La API exige el numero completo con
     * indicativo de pais, solo digitos.
     */
    private function normalizarTelefono(?string $telefono): ?string
    {
        if (empty($telefono)) {
            return null;
        }

        $digitos = preg_replace('/\D+/', '', $telefono);

        if ($digitos === '') {
            return null;
        }

        if (str_starts_with($digitos, '57') && strlen($digitos) === 12) {
            return $digitos;
        }

        if (strlen($digitos) === 10) {
            return '57'.$digitos;
        }

        return null;
    }
}
