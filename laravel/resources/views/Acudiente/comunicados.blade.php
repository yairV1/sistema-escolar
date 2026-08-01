@extends('layouts.panel')

@section('title', 'Comunicados')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-3">Comunicados</h1>

        @include('Acudiente.partials.selector-estudiante')

        @if ($comunicados->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-bullhorn"></i></div>
                <p class="mb-0">No hay comunicados relacionados con {{ $hijoActivo->nombres }} todavía.</p>
            </div>
        @else
            @php
                $tipoIconos = ['informativa' => ['fa-circle-info', 'bg-info-subtle text-info'], 'academica' => ['fa-graduation-cap', 'bg-primary-subtle text-primary'], 'disciplinaria' => ['fa-triangle-exclamation', 'bg-danger-subtle text-danger'], 'sistema' => ['fa-gear', 'bg-secondary-subtle text-secondary']];
            @endphp
            <div class="announcement-list">
                @foreach ($comunicados as $comunicado)
                    @php [$icono, $colorClase] = $tipoIconos[$comunicado->tipo] ?? ['fa-bullhorn', 'bg-secondary-subtle text-secondary']; @endphp
                    <div class="announcement-item {{ $comunicado->leido ? '' : 'announcement-item--unread' }}">
                        <div class="announcement-item__icon {{ $colorClase }}"><i class="fas {{ $icono }}"></i></div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2">
                                @if (! $comunicado->leido)
                                    <span class="announcement-item__dot"></span>
                                @endif
                                <span class="announcement-item__title">{{ $comunicado->titulo }}</span>
                            </div>
                            <div class="announcement-item__body">{{ $comunicado->mensaje }}</div>
                            <div class="announcement-item__meta">{{ $comunicado->fecha }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
