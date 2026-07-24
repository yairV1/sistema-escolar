<?php

use App\Modules\Auth\Models\Usuario;
use App\Modules\Calendario\Services\VisibilidadCalendarioService;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Modules.Auth.Models.Usuario.{id}', function (Usuario $usuario, int $id) {
    return $usuario->id_usuario === $id;
});

// Fase 4 (Calendario): eventos públicos de un curso — solo quien tiene ese
// curso en su alcance (VisibilidadCalendarioService, mismo cálculo que ya
// filtra el feed y el <select> del filtro) puede suscribirse.
Broadcast::channel('calendario.curso.{idCurso}', function (Usuario $usuario, int $idCurso) {
    $cursosVisibles = app(VisibilidadCalendarioService::class)->cursosVisibles($usuario);

    return $cursosVisibles === null || in_array($idCurso, $cursosVisibles, true);
});

// Eventos públicos sin curso asociado (Festivos, Eventos Institucionales
// generales) — cualquier autenticado puede suscribirse; el payload no
// incluye contenido (solo id_evento+accion), así que no hay fuga real.
Broadcast::channel('calendario.institucional', fn (Usuario $usuario) => true);
