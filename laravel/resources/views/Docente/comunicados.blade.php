@extends('layouts.panel')

@section('title', 'Comunicados')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">Comunicados</h1>

        @if ($comunicados->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-bullhorn"></i></div>
                <p class="mb-0">No has recibido comunicados todavía.</p>
            </div>
        @else
            @php $tipoColores = ['informativa' => 'info', 'academica' => 'primary', 'disciplinaria' => 'danger', 'pago' => 'warning', 'sistema' => 'secondary']; @endphp
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width:28px;"></th>
                            <th>Comunicado</th>
                            <th style="width:140px;">Tipo</th>
                            <th style="width:170px;">Enviado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($comunicados as $comunicado)
                            <tr class="{{ $comunicado->leida ? '' : 'bg-body-tertiary' }}"
                                @unless ($comunicado->leida)
                                    role="button" data-marcar-leido data-url="{{ route('docente.comunicados.leido', $comunicado) }}"
                                @endunless>
                                <td>
                                    @unless ($comunicado->leida)
                                        <span class="bg-primary rounded-circle d-inline-block" style="width:8px;height:8px;" title="Sin leer"></span>
                                    @endunless
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $comunicado->titulo }}</div>
                                    <div class="small text-secondary">{{ \Illuminate\Support\Str::limit($comunicado->mensaje, 90) }}</div>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $tipoColores[$comunicado->tipo_notificacion] ?? 'secondary' }}">
                                        {{ \App\Modules\Comunicados\Models\Notificacion::TIPOS_LABELS[$comunicado->tipo_notificacion] ?? $comunicado->tipo_notificacion }}
                                    </span>
                                </td>
                                <td class="text-secondary small text-nowrap">{{ $comunicado->fecha_envio->format('d/m/Y g:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $comunicados->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/docente/comunicados.js')
@endpush
