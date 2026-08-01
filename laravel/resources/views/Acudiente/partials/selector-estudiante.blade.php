{{--
    Selector persistente de estudiante para el panel de Acudiente.
    Espera $hijos (Collection de estudiantes a cargo) y $hijoActivo (el
    seleccionado). Cada opción navega a la MISMA ruta actual cambiando solo
    ?estudiante=, preservando el resto del query string (p.ej. ?periodo=).
    Sin JS propio: usa el dropdown nativo de Bootstrap (ya cargado en toda
    la app vía app.js).
--}}
<div class="dropdown child-selector mb-4">
    <button type="button" class="child-selector__toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="child-selector__avatar">{{ mb_substr($hijoActivo->nombres, 0, 1).mb_substr($hijoActivo->apellidos, 0, 1) }}</span>
        <span class="child-selector__info">
            <span class="d-block child-selector__name">{{ $hijoActivo->nombres }} {{ $hijoActivo->apellidos }}</span>
            <span class="d-block child-selector__meta">{{ $hijoActivo->curso }}</span>
        </span>
        <i class="fas fa-chevron-down ms-1 small text-secondary"></i>
    </button>

    <ul class="dropdown-menu">
        <li class="px-2 py-1 small text-secondary text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">Viendo a</li>
        @foreach ($hijos as $hijo)
            <li>
                <a class="child-option {{ $hijo->id === $hijoActivo->id ? 'active' : '' }}"
                   href="{{ route(request()->route()->getName(), array_merge(request()->query(), ['estudiante' => $hijo->id])) }}">
                    <span class="child-option__avatar">{{ mb_substr($hijo->nombres, 0, 1).mb_substr($hijo->apellidos, 0, 1) }}</span>
                    <span>
                        <span class="d-block child-option__name">{{ $hijo->nombres }} {{ $hijo->apellidos }}</span>
                        <span class="d-block child-option__meta">{{ $hijo->curso }}</span>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
