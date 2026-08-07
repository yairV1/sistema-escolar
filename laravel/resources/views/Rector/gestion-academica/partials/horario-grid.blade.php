{{--
    Grilla visual semanal de horarios (estilo "Class Schedule"). Espera
    $horarios: Collection<Horario> con asignacion.materia cargado.
    $puedeAgregar (opcional, default true): controla el modo editable completo
    — celdas vacías clicables para abrir #modalNuevoHorario, y cada bloque
    existente clicable (abre #modalEditarHorario) con botón de eliminar.
    En false, la grilla queda 100% de solo lectura (usado por las vistas de
    Estudiante/Acudiente).
    Reutilizada por la pestaña Horarios y por el detalle de curso.

    Color: cada materia se ancla a un slot fijo de la paleta categórica
    (--cat-1..8, ver _variables.scss) según su id_materia — así el mismo color
    identifica siempre a la misma materia, sin importar el curso o con qué
    otras materias comparta pantalla. El texto nunca usa ese color (solo tinta
    neutra); la identidad la llevan el borde izquierdo del bloque, el punto y
    la leyenda.
--}}
@php
    $puedeAgregar = $puedeAgregar ?? true;
    $diasOpciones = ['lunes' => 'Lun', 'martes' => 'Mar', 'miercoles' => 'Mié', 'jueves' => 'Jue', 'viernes' => 'Vie', 'sabado' => 'Sáb'];
    $diasCompletos = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'];
    $aMinutos = fn ($hora) => ((int) substr($hora, 0, 2)) * 60 + ((int) substr($hora, 3, 2));
    $totalSlots = 8;

    $inicioMin = 7 * 60;
    $finMin = 17 * 60;
    if ($horarios->isNotEmpty()) {
        $minReal = $horarios->min(fn ($h) => $aMinutos($h->hora_inicio));
        $maxReal = $horarios->max(fn ($h) => $aMinutos($h->hora_fin));
        $inicioMin = min($inicioMin, intdiv($minReal, 60) * 60);
        $finMin = max($finMin, intdiv($maxReal, 60) * 60 + ($maxReal % 60 > 0 ? 60 : 0));
    }

    $pxPorHora = 68;
    $totalHoras = intdiv($finMin - $inicioMin, 60);

    // Materias "conocidas" siempre usan el mismo tono, sin importar su
    // id_materia, para que el color coincida con lo que un docente/estudiante
    // ya espera (Matemáticas = azul, Español = verde, etc). El orden de los
    // patrones importa: "educación física" se revisa antes que "física" para
    // que no quede atrapada por el patrón más genérico. Cualquier materia que
    // no matchee ningún patrón cae en la paleta categórica por id_materia.
    $normalizar = fn ($texto) => str_replace(
        ['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'],
        mb_strtolower(trim($texto))
    );
    $patronesColor = [
        'educacion fisica' => 'var(--cat-2)', // naranja
        'matematic' => 'var(--cat-1)', // azul
        'espanol' => 'var(--cat-6)', // verde
        'lengua castellana' => 'var(--cat-6)', // verde
        'quimic' => 'var(--cat-4)', // amarillo
        'religion' => 'var(--cat-7)', // morado
        'fisica' => 'var(--cat-8)', // rojo
        'ingles' => 'var(--cat-9)', // celeste
    ];

    $colorMateria = [];
    $nombreMateria = [];
    foreach ($horarios as $h) {
        $idMateria = $h->asignacion->id_materia;
        if (! isset($colorMateria[$idMateria])) {
            $nombre = $h->asignacion->materia->nombre_materia;
            $nombreNormalizado = $normalizar($nombre);
            $color = null;
            foreach ($patronesColor as $patron => $variable) {
                if (str_contains($nombreNormalizado, $patron)) {
                    $color = $variable;
                    break;
                }
            }
            $colorMateria[$idMateria] = $color ?? 'var(--cat-'.(($idMateria % $totalSlots) + 1).')';
            $nombreMateria[$idMateria] = $nombre;
        }
    }

    $horariosPorDia = $horarios->groupBy('dia_semana');

    $diaHoy = match (now()->dayOfWeek) {
        1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado',
        default => null,
    };
    $minutoAhora = now()->hour * 60 + now()->minute;
    $mostrarAhora = $diaHoy && $minutoAhora >= $inicioMin && $minutoAhora <= $finMin;
    $topAhora = ($minutoAhora - $inicioMin) / 60 * $pxPorHora;
@endphp

<div class="horario-week-scroll d-none d-md-block">
    <div class="horario-week">
        <div class="horario-week__header">
            <div class="horario-week__hours-spacer"></div>
            @foreach ($diasOpciones as $diaValor => $diaEtiqueta)
                <div class="horario-week__day-name {{ $diaValor === $diaHoy ? 'horario-week__day-name--hoy' : '' }}">{{ $diaEtiqueta }}</div>
            @endforeach
        </div>
        <div class="horario-week__body" style="height: {{ $totalHoras * $pxPorHora }}px;">
            <div class="horario-week__hours">
                @for ($m = $inicioMin; $m < $finMin; $m += 60)
                    <div class="horario-week__hour-label" style="height: {{ $pxPorHora }}px;">
                        {{ sprintf('%d:%02d', intdiv($m, 60), $m % 60) }}
                    </div>
                @endfor
            </div>

            @if ($mostrarAhora)
                <div class="horario-week__ahora-hora" style="top: {{ $topAhora }}px;">
                    {{ sprintf('%d:%02d', intdiv($minutoAhora, 60), $minutoAhora % 60) }}
                </div>
            @endif

            @foreach ($diasOpciones as $diaValor => $diaEtiqueta)
                <div class="horario-week__day {{ $diaValor === $diaHoy ? 'horario-week__day--hoy' : '' }}">
                    @for ($m = $inicioMin; $m < $finMin; $m += 60)
                        <div class="horario-week__hour-line{{ $puedeAgregar ? ' horario-week__hour-line--clicable' : '' }}"
                             style="height: {{ $pxPorHora }}px;"
                             @if ($puedeAgregar)
                                 data-bs-toggle="modal" data-bs-target="#modalNuevoHorario"
                                 data-dia="{{ $diaValor }}" data-hora-inicio="{{ sprintf('%02d:00', intdiv($m, 60)) }}"
                             @endif
                        ></div>
                    @endfor

                    @if ($mostrarAhora && $diaValor === $diaHoy)
                        <div class="horario-week__ahora-linea" style="top: {{ $topAhora }}px;"></div>
                    @endif

                    @foreach ($horariosPorDia->get($diaValor, collect()) as $horario)
                        @php
                            $ini = $aMinutos($horario->hora_inicio);
                            $fin = $aMinutos($horario->hora_fin);
                            $top = ($ini - $inicioMin) / 60 * $pxPorHora;
                            $height = max(30, ($fin - $ini) / 60 * $pxPorHora);
                            $color = $colorMateria[$horario->asignacion->id_materia] ?? 'var(--bs-secondary-color)';
                            $profesorNombre = trim($horario->asignacion->profesor->usuario->nombres.' '.$horario->asignacion->profesor->usuario->apellidos);
                            $tituloCompleto = "{$horario->asignacion->materia->nombre_materia} · {$profesorNombre}"
                                .' · '.substr($horario->hora_inicio, 0, 5).'–'.substr($horario->hora_fin, 0, 5)
                                .($horario->salon ? " · Salón {$horario->salon}" : '');
                        @endphp
                        <div class="horario-bloque"
                             style="top: {{ $top }}px; height: {{ $height }}px; --bloque-color: {{ $color }};"
                             @if ($puedeAgregar)
                                 data-bs-toggle="modal" data-bs-target="#modalEditarHorario{{ $horario->id_horario }}"
                                 role="button" tabindex="0"
                             @endif
                             title="{{ $tituloCompleto }}">
                            @if ($puedeAgregar)
                                <button type="button" class="horario-bloque__del" data-desactivar
                                        data-url="{{ route('gestion-academica.horarios.desactivar', $horario) }}"
                                        data-nombre="este horario"
                                        title="Quitar" onclick="event.stopPropagation()">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            @endif
                            <div class="horario-bloque__hora">{{ substr($horario->hora_inicio, 0, 5) }}–{{ substr($horario->hora_fin, 0, 5) }}</div>
                            <div class="horario-bloque__materia">
                                <span class="horario-bloque__dot"></span>
                                <span class="horario-bloque__materia-nombre">{{ $horario->asignacion->materia->nombre_materia }}</span>
                            </div>
                            <div class="horario-bloque__meta">
                                <span><i class="fas fa-user"></i> {{ $profesorNombre }}</span>
                                @if ($horario->salon)
                                    <span><i class="fas fa-location-dot"></i> {{ $horario->salon }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Vista móvil: lista vertical agrupada por día. La cuadrícula de arriba
     se oculta (d-none d-md-block) y esta lista toma su lugar por debajo de
     los breakpoints md, sin volver a golpear al servidor. --}}
<div class="horario-list d-md-none">
    @foreach ($diasOpciones as $diaValor => $diaEtiqueta)
        @php $clasesDia = $horariosPorDia->get($diaValor, collect())->sortBy('hora_inicio'); @endphp
        <div class="horario-list__dia {{ $diaValor === $diaHoy ? 'horario-list__dia--hoy' : '' }}">
            <div class="horario-list__dia-header">
                <span>{{ $diasCompletos[$diaValor] }}</span>
                @if ($diaValor === $diaHoy)
                    <span class="horario-list__badge-hoy">Hoy</span>
                @endif
            </div>
            @if ($clasesDia->isEmpty())
                <div class="horario-list__vacio">Sin clases programadas.</div>
            @else
                @foreach ($clasesDia as $horario)
                    @php
                        $colorMovil = $colorMateria[$horario->asignacion->id_materia] ?? 'var(--bs-secondary-color)';
                        $profesorMovil = trim($horario->asignacion->profesor->usuario->nombres.' '.$horario->asignacion->profesor->usuario->apellidos);
                    @endphp
                    <div class="horario-list__item" style="--bloque-color: {{ $colorMovil }};"
                         @if ($puedeAgregar)
                             data-bs-toggle="modal" data-bs-target="#modalEditarHorario{{ $horario->id_horario }}"
                             role="button" tabindex="0"
                         @endif
                    >
                        <div class="horario-list__hora">{{ substr($horario->hora_inicio, 0, 5) }}<br>{{ substr($horario->hora_fin, 0, 5) }}</div>
                        <div class="horario-list__info">
                            <div class="horario-list__materia">
                                <span class="horario-list__dot"></span>
                                {{ $horario->asignacion->materia->nombre_materia }}
                            </div>
                            <div class="horario-list__meta">
                                <span>{{ $profesorMovil }}</span>
                                @if ($horario->salon)
                                    <span>{{ $horario->salon }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
</div>

@if ($colorMateria)
    <div class="horario-week__leyenda">
        @foreach ($colorMateria as $idMateria => $color)
            <span class="horario-week__leyenda-item">
                <span class="horario-week__leyenda-dot" style="--bloque-color: {{ $color }};"></span>
                {{ $nombreMateria[$idMateria] }}
            </span>
        @endforeach
    </div>
@endif
