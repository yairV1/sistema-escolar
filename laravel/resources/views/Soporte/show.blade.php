@extends(auth()->user()?->esSuperAdmin() ? 'layouts.superadmin' : (in_array(auth()->user()?->rolSlug, ['admin', 'rector']) ? 'layouts.rector' : (auth()->user()?->rolSlug === 'docente' ? 'layouts.docente' : 'layouts.panel')))

@section('title', 'Soporte — '.$soporte->asunto)

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('soportes.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1">
                <h1 class="h4 fw-semibold font-serif mb-0">{{ $soporte->asunto }}</h1>
                <div class="small text-secondary">
                    {{ trim($soporte->remitente->nombres.' '.$soporte->remitente->apellidos) }} ({{ $soporte->remitente->rolLabel }})
                    · {{ $soporte->created_at->format('d/m/Y g:i A') }}
                </div>
            </div>

            @php $estadoColores = ['nuevo' => 'warning', 'leido' => 'info', 'resuelto' => 'success']; @endphp
            <span class="badge text-bg-{{ $estadoColores[$soporte->estado] }}">{{ ucfirst($soporte->estado) }}</span>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-message text-primary me-1"></i> Mensaje</h2>
                        <p class="{{ $soporte->imagen ? 'mb-3' : 'mb-0' }}" style="white-space: pre-line;">{{ $soporte->mensaje }}</p>
                        @if ($soporte->imagen)
                            <a href="{{ $soporte->imagenUrl }}" target="_blank" rel="noopener">
                                <img src="{{ $soporte->imagenUrl }}" alt="Captura adjunta"
                                     class="img-fluid rounded border" style="max-height: 420px;">
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-route text-primary me-1"></i> Seguimiento</h2>

                        @php $yaLeido = in_array($soporte->estado, ['leido', 'resuelto'], true); @endphp
                        <ul class="soporte-seguimiento">
                            <li class="soporte-seguimiento-item is-completo">
                                <span class="soporte-seguimiento-punto"></span>
                                <div>
                                    <div class="soporte-seguimiento-etiqueta">Creado</div>
                                    <div class="small text-secondary">
                                        {{ trim($soporte->remitente->nombres.' '.$soporte->remitente->apellidos) }}
                                        · {{ $soporte->created_at->format('d/m/Y g:i A') }}
                                    </div>
                                </div>
                            </li>
                            <li class="soporte-seguimiento-item {{ $yaLeido ? 'is-completo' : '' }}">
                                <span class="soporte-seguimiento-punto"></span>
                                <div>
                                    <div class="soporte-seguimiento-etiqueta">Leído por soporte</div>
                                    <div class="small text-secondary">{{ $yaLeido ? 'Visto' : 'Pendiente' }}</div>
                                </div>
                            </li>
                            <li class="soporte-seguimiento-item {{ $soporte->estado === 'resuelto' ? 'is-completo' : '' }}">
                                <span class="soporte-seguimiento-punto"></span>
                                <div>
                                    <div class="soporte-seguimiento-etiqueta">Resuelto</div>
                                    <div class="small text-secondary">
                                        @if ($soporte->estado === 'resuelto')
                                            {{ trim($soporte->resueltoPor->nombres.' '.$soporte->resueltoPor->apellidos) }}
                                            · {{ $soporte->resuelto_at->format('d/m/Y g:i A') }}
                                        @else
                                            Pendiente
                                        @endif
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                @unless ($soporte->estado === 'resuelto')
                    <div class="card">
                        <div class="card-body">
                            <p class="text-secondary small mb-3">Cuando termines de atender esta solicitud, marcala como resuelta.</p>
                            <button type="button" class="btn btn-primary w-100" id="btnMarcarResuelto" data-url="{{ route('soportes.resolver', $soporte) }}">
                                <i class="fas fa-check me-1"></i> Marcar como resuelto
                            </button>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/soporte/soporte.js')
@endpush
