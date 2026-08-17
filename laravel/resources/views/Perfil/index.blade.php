@extends(auth()->user()?->esSuperAdmin() ? 'layouts.superadmin' : (in_array(auth()->user()?->rolSlug, ['admin', 'rector']) ? 'layouts.rector' : (auth()->user()?->rolSlug === 'docente' ? 'layouts.docente' : 'layouts.panel')))

@section('title', __('Mi perfil'))

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <h1 class="h4 fw-semibold font-serif mb-4">{{ __('Mi perfil') }}</h1>

        @php
            $esFamilia = in_array(auth()->user()->rolSlug, ['estudiante', 'acudiente'], true);
            $inicialesPerfil = collect(explode(' ', trim($usuario->nombres.' '.$usuario->apellidos)))
                ->filter()
                ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
                ->join('');
            $inicialesPerfil = mb_substr($inicialesPerfil, 0, 2) ?: '?';

            // Color por defecto del rol, solo para precargar el picker cuando el
            // usuario todavía no eligió uno propio — ver App\Shared\AccentColor.
            $colorRolDefault = match (true) {
                in_array(auth()->user()->rolSlug, ['admin', 'rector']) => '#6f5cf6',
                auth()->user()->rolSlug === 'docente' => '#0f9b8e',
                default => '#2d7a4f',
            };
        @endphp

        <div class="row g-3">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body p-2">
                        <div class="nav flex-column perfil-tabs" role="tablist" aria-orientation="vertical">
                            <button class="perfil-tab active" data-bs-toggle="pill" data-bs-target="#tabDatos" type="button" role="tab" aria-selected="true">
                                <i class="fas fa-user"></i> {{ __('Datos personales') }}
                            </button>
                            <button class="perfil-tab" data-bs-toggle="pill" data-bs-target="#tabPassword" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-lock"></i> {{ __('Contraseña') }}
                            </button>
                            <button class="perfil-tab" data-bs-toggle="pill" data-bs-target="#tabVerificacion" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-shield-halved"></i> {{ __('Verificación en dos pasos') }}
                            </button>
                            <button class="perfil-tab" data-bs-toggle="pill" data-bs-target="#tabConfiguracion" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-sliders"></i> {{ __('Configuración') }}
                            </button>
                            @if ($esFamilia)
                                <button class="perfil-tab" data-bs-toggle="pill" data-bs-target="#tabFamilia" type="button" role="tab" aria-selected="false">
                                    <i class="fas fa-people-roof"></i> {{ __('Mi familia') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="tab-content">

                    {{-- ---------- Datos personales ---------- --}}
                    <div class="tab-pane fade show active" id="tabDatos" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">{{ __('Datos personales') }}</h2>
                                <form id="formPerfil" data-url="{{ route('perfil.update') }}" novalidate>
                                    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                                        <div class="avatar-dropzone avatar-dropzone-lg" id="avatarDropzone" tabindex="0" role="button" aria-label="Cambiar foto de perfil">
                                            <img src="{{ $usuario->fotoPerfilUrl }}" alt="Foto de perfil" id="avatarPreview" class="{{ $usuario->fotoPerfilUrl ? '' : 'd-none' }}">
                                            <span class="avatar-initials {{ $usuario->fotoPerfilUrl ? 'd-none' : '' }}" id="avatarInitials">{{ $inicialesPerfil }}</span>
                                            <div class="dz-overlay"><i class="fas fa-camera"></i></div>
                                            <input type="file" name="foto_perfil" id="avatarInput" accept="image/*" class="d-none" data-feedback="err-foto_perfil">
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-primary" id="avatarUploadBtn">
                                                    <i class="fas fa-upload me-1"></i> {{ __('Subir nueva foto') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="avatarRemoveBtn" style="{{ $usuario->fotoPerfilUrl ? '' : 'display:none' }}">
                                                    <i class="fas fa-trash me-1"></i> {{ __('Quitar foto') }}
                                                </button>
                                            </div>
                                            <input type="hidden" name="foto_perfil_removido" id="avatarRemovido" value="0">
                                            <div class="invalid-feedback d-block" id="err-foto_perfil"></div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nombres *</label>
                                            <input type="text" class="form-control" name="nombres" value="{{ $usuario->nombres }}" data-feedback="err-p-nombres" required>
                                            <div class="invalid-feedback" id="err-p-nombres"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Apellidos *</label>
                                            <input type="text" class="form-control" name="apellidos" value="{{ $usuario->apellidos }}" data-feedback="err-p-apellidos" required>
                                            <div class="invalid-feedback" id="err-p-apellidos"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Correo *</label>
                                            <input type="email" class="form-control" name="correo" value="{{ $usuario->correo }}" data-feedback="err-p-correo" required>
                                            <div class="invalid-feedback" id="err-p-correo"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" name="telefono" value="{{ $usuario->telefono }}" data-feedback="err-p-telefono">
                                            <div class="invalid-feedback" id="err-p-telefono"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Documento</label>
                                            <input type="text" class="form-control" value="{{ strtoupper($usuario->tipo_documento ?? '') }} {{ $usuario->numero_documento }}" disabled>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-primary" id="btnGuardarPerfil">
                                            <i class="fas fa-save me-1"></i> {{ __('Guardar cambios') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- Contraseña ---------- --}}
                    <div class="tab-pane fade" id="tabPassword" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">{{ __('Cambiar contraseña') }}</h2>
                                <form id="formPassword" data-url="{{ route('perfil.password') }}" novalidate>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Contraseña actual') }} *</label>
                                            <input type="password" class="form-control" name="password_actual" data-feedback="err-pw-actual" required>
                                            <div class="invalid-feedback" id="err-pw-actual"></div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">{{ __('Contraseña nueva') }} *</label>
                                            <input type="password" class="form-control" name="password_nueva" minlength="8" data-feedback="err-pw-nueva" required>
                                            <div class="invalid-feedback" id="err-pw-nueva"></div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">{{ __('Confirmar') }} *</label>
                                            <input type="password" class="form-control" name="password_nueva_confirmation" minlength="8" required>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-primary" id="btnCambiarPassword">
                                            <i class="fas fa-key me-1"></i> {{ __('Cambiar contraseña') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- Verificación en dos pasos ---------- --}}
                    <div class="tab-pane fade" id="tabVerificacion" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body" id="dosFactoresPanel" data-activo="{{ $dosFactoresActivo ? '1' : '0' }}"
                                         data-enable-url="{{ route('perfil.2fa.enable') }}"
                                         data-confirm-url="{{ route('perfil.2fa.confirm') }}"
                                         data-disable-url="{{ route('perfil.2fa.disable') }}"
                                         data-regenerar-url="{{ route('perfil.2fa.regenerar-codigos') }}">

                                        @if ($dosFactoresActivo)
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <span class="badge text-bg-success">{{ __('Activo') }}</span>
                                                <span class="text-secondary small">{{ __('Tu cuenta está protegida con un segundo factor.') }}</span>
                                            </div>

                                            <button type="button" class="btn btn-outline-secondary btn-sm mb-2" id="btnRegenerarCodigos">
                                                <i class="fas fa-rotate me-1"></i> {{ __('Regenerar códigos de recuperación') }}
                                            </button>

                                            <form id="formDeshabilitar2fa" class="mt-3 border-top pt-3">
                                                <label class="form-label">{{ __('Contraseña actual (para desactivar 2FA)') }}</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" name="password" id="passwordDeshabilitar"
                                                           data-feedback="err-password-deshabilitar" required>
                                                    <button type="submit" class="btn btn-outline-danger">{{ __('Desactivar 2FA') }}</button>
                                                </div>
                                                <div class="invalid-feedback" id="err-password-deshabilitar"></div>
                                            </form>
                                        @else
                                            <p class="text-secondary small">
                                                {{ __('Añade una capa extra de seguridad: además de tu contraseña, se pedirá un código de tu app autenticadora (Google Authenticator, Authy, etc.) al iniciar sesión.') }}
                                            </p>
                                            <button type="button" class="btn btn-primary" id="btnHabilitar2fa">
                                                <i class="fas fa-lock me-1"></i> {{ __('Activar 2FA') }}
                                            </button>

                                            <div id="bloqueEnrolamiento" class="d-none mt-4">
                                                <div class="text-center mb-3" id="qrContenedor"></div>
                                                <p class="small text-secondary text-center">
                                                    {{ __('Escanea el código QR o ingresa el secreto manualmente:') }} <code id="secretoTexto"></code>
                                                </p>
                                                <form id="formConfirmar2fa">
                                                    <label class="form-label">{{ __('Código de 6 dígitos') }}</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="codigo" id="codigoConfirmar"
                                                               maxlength="6" data-feedback="err-codigo-confirmar" required>
                                                        <button type="submit" class="btn btn-primary">{{ __('Confirmar') }}</button>
                                                    </div>
                                                    <div class="invalid-feedback" id="err-codigo-confirmar"></div>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card d-none" id="cardCodigosRecuperacion">
                                    <div class="card-body">
                                        <h2 class="h6 fw-semibold mb-2"><i class="fas fa-key text-warning me-1"></i> {{ __('Códigos de recuperación') }}</h2>
                                        <p class="text-secondary small">
                                            {{ __('Guárdalos en un lugar seguro: cada uno sirve una sola vez si pierdes acceso a tu app autenticadora. No se volverán a mostrar.') }}
                                        </p>
                                        <ul class="list-group list-group-flush" id="listaCodigosRecuperacion"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- Configuración ---------- --}}
                    <div class="tab-pane fade" id="tabConfiguracion" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">{{ __('Configuración') }}</h2>
                                <form id="formPreferencias" data-url="{{ route('perfil.preferencias') }}" novalidate>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Idioma') }}</label>
                                            <select class="form-select" name="idioma">
                                                <option value="es" @selected($usuario->idioma === 'es')>Español</option>
                                                <option value="en" @selected($usuario->idioma === 'en')>English</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Tema') }}</label>
                                            <select class="form-select" name="tema" id="prefTemaSelect">
                                                <option value="light" @selected($usuario->tema === 'light')>{{ __('Claro') }}</option>
                                                <option value="dark" @selected($usuario->tema === 'dark')>{{ __('Oscuro') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="notificaciones_email" id="prefNotifEmail" value="1" @checked($usuario->notificaciones_email)>
                                                <label class="form-check-label" for="prefNotifEmail">{{ __('Recibir notificaciones por correo') }}</label>
                                            </div>
                                        </div>
                                        @unless (auth()->user()->esSuperAdmin())
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Color de acento') }}</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" class="form-control form-control-color" name="color_acento" id="prefColorAcento"
                                                           value="{{ $usuario->color_acento ?? $colorRolDefault }}" data-default="{{ $colorRolDefault }}"
                                                           title="{{ __('Elegí tu color') }}">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="prefColorReset">
                                                        {{ __('Restablecer color del rol') }}
                                                    </button>
                                                </div>
                                                <input type="hidden" name="color_acento_restablecido" id="colorAcentoRestablecido" value="0">
                                            </div>
                                        @endunless
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-primary" id="btnGuardarPreferencias">
                                            <i class="fas fa-save me-1"></i> {{ __('Guardar preferencias') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- Mi familia (solo estudiante/acudiente) ---------- --}}
                    @if ($esFamilia)
                        <div class="tab-pane fade" id="tabFamilia" role="tabpanel">
                            @if (auth()->user()->rolSlug === 'estudiante')
                                <div class="card">
                                    <div class="card-body">
                                        <h2 class="h6 fw-semibold mb-3"><i class="fas fa-people-roof text-primary me-1"></i> Acudiente asociado</h2>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="child-selector__avatar" style="width:44px;height:44px;font-size:.85rem;">
                                                {{ collect(explode(' ', $acudiente->nombre))->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->join('') }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $acudiente->nombre }}</div>
                                                <div class="small text-secondary">{{ $acudiente->parentesco }}</div>
                                            </div>
                                        </div>
                                        <dl class="row mt-3 mb-0">
                                            <dt class="col-sm-3 text-secondary fw-normal">Teléfono</dt>
                                            <dd class="col-sm-9">{{ $acudiente->telefono }}</dd>
                                            <dt class="col-sm-3 text-secondary fw-normal">Correo</dt>
                                            <dd class="col-sm-9 mb-0">{{ $acudiente->correo }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            @else
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h2 class="h6 fw-semibold mb-0"><i class="fas fa-user-graduate text-primary me-1"></i> Estudiantes vinculados</h2>
                                            <a href="{{ route('acudiente.estudiantes') }}" class="small">Ver todos</a>
                                        </div>

                                        @if ($hijos->isEmpty())
                                            <div class="empty-state">
                                                <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
                                                <p class="mb-0">No tienes estudiantes vinculados todavía.</p>
                                            </div>
                                        @else
                                            <ul class="list-group list-group-flush">
                                                @foreach ($hijos as $hijo)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <span class="child-option__avatar">{{ mb_substr($hijo->nombres, 0, 1).mb_substr($hijo->apellidos, 0, 1) }}</span>
                                                            <div>
                                                                <div class="fw-semibold">{{ $hijo->nombres }} {{ $hijo->apellidos }}</div>
                                                                <div class="small text-secondary">{{ $hijo->curso }} · {{ ucfirst($hijo->parentesco) }}</div>
                                                            </div>
                                                        </div>
                                                        @if ($hijo->es_principal)
                                                            <span class="badge text-bg-success">Principal</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/perfil/perfil.js')
@endpush
