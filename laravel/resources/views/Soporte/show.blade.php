@extends('layouts.panel')

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
                        <p class="mb-0" style="white-space: pre-line;">{{ $soporte->mensaje }}</p>

                        @if ($soporte->imagen_ruta)
                            <hr>
                            <h2 class="h6 fw-semibold mb-3"><i class="fas fa-image text-primary me-1"></i> Captura adjunta</h2>
                            <a href="{{ Illuminate\Support\Facades\Storage::disk('public')->url($soporte->imagen_ruta) }}" target="_blank" rel="noopener">
                                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($soporte->imagen_ruta) }}"
                                     alt="{{ $soporte->imagen_nombre_original }}" class="img-fluid rounded border" style="max-height: 400px;">
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        @if ($soporte->estado === 'resuelto')
                            <div class="empty-state py-3">
                                <div class="empty-icon"><i class="fas fa-circle-check"></i></div>
                                <p class="mb-1 fw-semibold">Resuelto</p>
                                <p class="mb-0 small">
                                    por {{ trim($soporte->resueltoPor->nombres.' '.$soporte->resueltoPor->apellidos) }}
                                    · {{ $soporte->resuelto_at->format('d/m/Y g:i A') }}
                                </p>
                            </div>
                        @else
                            <p class="text-secondary small mb-3">Cuando termines de atender esta solicitud, marcala como resuelta.</p>
                            <button type="button" class="btn btn-primary w-100" id="btnMarcarResuelto" data-url="{{ route('soportes.resolver', $soporte) }}">
                                <i class="fas fa-check me-1"></i> Marcar como resuelto
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/soporte/soporte.js')
@endpush
