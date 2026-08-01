<?php

namespace App\Core\Seguridad;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * TOTP (RFC 6238) implementado con hash_hmac puro, sin librería externa
 * para el algoritmo — solo el renderizado del QR usa endroid/qr-code
 * (SvgWriter: no depende de la extensión GD, ausente en este entorno).
 * Uso exclusivo por ahora: cuentas SuperAdmin (ver TwoFactorController),
 * pero implementado sobre `usuarios` en general para no atarlo a un rol.
 */
class TwoFactorService
{
    private const DIGITOS = 6;
    private const PERIODO_SEGUNDOS = 30;
    private const VENTANA_PASOS = 1; // tolera +/- 1 paso (30s) de desfase de reloj

    public function generarSecreto(): string
    {
        $bytes = random_bytes(20);

        return $this->base32Encode($bytes);
    }

    public function provisioningUri(string $secreto, string $email, string $emisor = 'Gestión Académica'): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            rawurlencode($emisor),
            rawurlencode($email),
            $secreto,
            rawurlencode($emisor),
            self::DIGITOS,
            self::PERIODO_SEGUNDOS,
        );
    }

    public function qrSvg(string $provisioningUri): string
    {
        $builder = new Builder(
            writer: new SvgWriter(),
            data: $provisioningUri,
            size: 240,
            margin: 8,
        );

        return $builder->build()->getString();
    }

    public function verificar(string $secreto, string $codigo): bool
    {
        $codigo = trim($codigo);

        if (! preg_match('/^\d{6}$/', $codigo)) {
            return false;
        }

        $pasoActual = intdiv(time(), self::PERIODO_SEGUNDOS);

        for ($offset = -self::VENTANA_PASOS; $offset <= self::VENTANA_PASOS; $offset++) {
            if (hash_equals($this->generarCodigo($secreto, $pasoActual + $offset), $codigo)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<int, string> */
    public function generarCodigosRecuperacion(int $cantidad = 8): array
    {
        return array_map(
            fn () => strtoupper(bin2hex(random_bytes(5))),
            range(1, $cantidad),
        );
    }

    private function generarCodigo(string $secretoBase32, int $paso): string
    {
        $clave = $this->base32Decode($secretoBase32);
        $contador = pack('N*', 0, $paso); // 8 bytes big-endian

        $hash = hash_hmac('sha1', $contador, $clave, true);
        $offset = ord($hash[19]) & 0x0F;

        $binario = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binario % (10 ** self::DIGITOS)), self::DIGITOS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $bytes): string
    {
        $alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $salida = '';
        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $salida .= $alfabeto[bindec($chunk)];
        }

        return $salida;
    }

    private function base32Decode(string $base32): string
    {
        $alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32 = strtoupper(rtrim($base32, '='));

        $bits = '';
        foreach (str_split($base32) as $char) {
            $posicion = strpos($alfabeto, $char);
            if ($posicion === false) {
                continue;
            }
            $bits .= str_pad(decbin($posicion), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) < 8) {
                continue;
            }
            $bytes .= chr(bindec($chunk));
        }

        return $bytes;
    }
}
