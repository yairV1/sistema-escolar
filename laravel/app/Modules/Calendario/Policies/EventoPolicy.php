<?php

namespace App\Modules\Calendario\Policies;

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Models\EventoCategoria;
use App\Modules\Calendario\Services\VisibilidadCalendarioService;

/**
 * Primera Policy real del proyecto. Autodescubierta por Laravel en
 * App\Modules\Calendario\Policies\EventoPolicy (confirmado contra
 * Gate::guessPolicyName()) — no requiere registro manual en un provider.
 */
class EventoPolicy
{
    public function __construct(private VisibilidadCalendarioService $visibilidad) {}

    public function view(Usuario $usuario, Evento $evento): bool
    {
        return $this->visibilidad->puedeVerEvento($usuario, $evento);
    }

    public function create(Usuario $usuario, EventoCategoria $categoria): bool
    {
        if ($categoria->es_sistema) {
            return false;
        }

        return in_array($usuario->rolSlug, $categoria->roles_crear, true);
    }

    public function update(Usuario $usuario, Evento $evento): bool
    {
        return $evento->id_usuario_creador === $usuario->id_usuario
            || in_array($usuario->rolSlug, $evento->categoria?->roles_editar ?? [], true);
    }

    public function delete(Usuario $usuario, Evento $evento): bool
    {
        return $evento->id_usuario_creador === $usuario->id_usuario
            || in_array($usuario->rolSlug, $evento->categoria?->roles_eliminar ?? [], true);
    }
}
