<?php

namespace App\Shared;

/**
 * Deriva los tokens de color (--sb-primary y compañía, ver _rector.scss/
 * _docente.scss/_panel.scss) a partir de un único hex elegido por el
 * usuario en Mi perfil → Configuración. Matemática de color pura, sin
 * dependencias de Laravel, para poder razonarla/testearla aislada.
 */
class AccentColor
{
    private const HEX_PATTERN = '/^#([0-9a-fA-F]{6})$/';

    /**
     * Atributo `style` listo para el <body> de un layout. Devuelve '' si
     * $hex es null o no es un hex de 6 dígitos válido (el usuario nunca
     * eligió color propio, o el dato está corrupto) — en ese caso el
     * layout se queda con los tokens por defecto de su rol, sin overrides.
     */
    public static function inlineStyle(?string $hex): string
    {
        if ($hex === null || ! preg_match(self::HEX_PATTERN, $hex)) {
            return '';
        }

        $t = self::tokens($hex);

        return "--sb-primary:{$t['primary']};"
            ."--sb-primary-dark:{$t['primaryDark']};"
            ."--sb-primary-light:{$t['primaryLight']};"
            ."--sb-on-primary:{$t['onPrimary']};"
            ."--bs-primary:{$t['primary']};"
            ."--bs-primary-rgb:{$t['rgb']};"
            ."--hero-gradient:{$t['heroGradient']};";
    }

    /** @return array{primary: string, primaryDark: string, primaryLight: string, onPrimary: string, rgb: string, heroGradient: string} */
    public static function tokens(string $hex): array
    {
        [$r, $g, $b] = self::hexToRgb($hex);
        [$h, $s, $l] = self::rgbToHsl($r, $g, $b);

        $primary = self::rgbToHex($r, $g, $b);
        $primaryDark = self::hslToHex($h, $s, max(0, $l - 0.18));
        $primaryLight = self::hslToHex($h, min($s, 0.35), 0.94);
        $onPrimary = self::perceivedBrightness($r, $g, $b) > 155 ? '#14213d' : '#ffffff';

        return [
            'primary' => $primary,
            'primaryDark' => $primaryDark,
            'primaryLight' => $primaryLight,
            'onPrimary' => $onPrimary,
            'rgb' => "{$r}, {$g}, {$b}",
            'heroGradient' => "linear-gradient(135deg, {$primary} 0%, {$primaryDark} 100%)",
        ];
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /** Fórmula YIQ simplificada: umbral estándar 155/255 para decidir texto claro u oscuro. */
    private static function perceivedBrightness(int $r, int $g, int $b): float
    {
        return ($r * 299 + $g * 587 + $b * 114) / 1000;
    }

    /** @return array{0: float, 1: float, 2: float} H/S/L en [0,1] */
    private static function rgbToHsl(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        $h = match ($max) {
            $r => ($g - $b) / $d + ($g < $b ? 6 : 0),
            $g => ($b - $r) / $d + 2,
            default => ($r - $g) / $d + 4,
        };
        $h /= 6;

        return [$h, $s, $l];
    }

    private static function hslToHex(float $h, float $s, float $l): string
    {
        if ($s === 0.0) {
            $v = (int) round($l * 255);

            return self::rgbToHex($v, $v, $v);
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $r = self::hueToRgb($p, $q, $h + 1 / 3);
        $g = self::hueToRgb($p, $q, $h);
        $b = self::hueToRgb($p, $q, $h - 1 / 3);

        return self::rgbToHex(
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255),
        );
    }

    private static function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }

        return $p;
    }
}
