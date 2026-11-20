<?php

namespace App\Modules\Instituciones\Support;

use Illuminate\Support\Str;

class SlugInstitucion
{
    public const FORMATO = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public static function normalizar(string $slug): string
    {
        return Str::slug($slug, '-');
    }

    public static function esCanonico(string $slug): bool
    {
        return preg_match(self::FORMATO, $slug) === 1 && self::normalizar($slug) === $slug;
    }

    public static function colisiona(string $slug, ?int $exceptoId = null): bool
    {
        $consulta = \Illuminate\Support\Facades\DB::table('instituciones')
            ->select('slug');

        if ($exceptoId !== null) {
            $consulta->where('id_institucion', '<>', $exceptoId);
        }

        $normalizado = self::normalizar($slug);

        return $consulta->get()->contains(fn ($fila) => self::normalizar((string) $fila->slug) === $normalizado);
    }

    public static function regla(?int $exceptoId = null): \Closure
    {
        return static function (string $atributo, mixed $valor, \Closure $fallar) use ($exceptoId): void {
            if (! is_string($valor) || $valor === '') {
                return;
            }

            if ($exceptoId !== null && \Illuminate\Support\Facades\DB::table('instituciones')
                ->where('id_institucion', $exceptoId)->value('slug') === $valor) {
                return;
            }

            if (! self::esCanonico($valor)) {
                $fallar('El slug debe usar minúsculas, números y guiones simples.');

                return;
            }

            if (self::colisiona($valor, $exceptoId)) {
                $fallar('El slug ya existe o colisiona al normalizarse.');
            }
        };
    }
}
