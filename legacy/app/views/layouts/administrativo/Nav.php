<?php
$navUsuario = Auth::usuario();
$navNombreCompleto = $navUsuario ? trim($navUsuario['nombres'] . ' ' . $navUsuario['apellidos']) : 'Invitado';
$navIniciales = '';
foreach (explode(' ', $navNombreCompleto) as $parte) {
    if ($parte !== '') {
        $navIniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
    }
}
$navIniciales = mb_substr($navIniciales, 0, 2) ?: '?';
$navRolLabels = [
    'admin'  => 'Administrador',
    'rector' => 'Directivo',
];
$navRolLabel = $navRolLabels[$navUsuario['rol'] ?? ''] ?? 'Panel';
?>

<header class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="topbar-menu" id="topbarMenu" aria-label="Menú">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-breadcrumb">
                <span class="breadcrumb-root">Panel <?= htmlspecialchars($navRolLabel, ENT_QUOTES, 'UTF-8') ?></span>
                <i class="fas fa-chevron-right"></i>
                <span class="breadcrumb-page" id="breadcrumbPage">Inicio</span>
            </div>
        </div>
        <div class="topbar-right">
            <!-- Buscador -->
            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar estudiante, docente…" id="adminSearch" />
                <kbd>Ctrl+K</kbd>
            </div>
            <!-- Notificaciones -->
            <button class="topbar-icon-btn notif" id="notiBtn" title="Notificaciones">
                <i class="fas fa-bell"></i>
                <span class="topbar-dot"></span>
            </button>
            <!-- Perfil -->
            <div class="topbar-profile" id="topbarProfile">
                <button class="topbar-avatar-btn" id="topbarAvatarBtn">
                    <div class="topbar-avatar"><?= htmlspecialchars($navIniciales, ENT_QUOTES, 'UTF-8') ?></div>
                    <span class="topbar-avatar-name"><?= htmlspecialchars($navNombreCompleto, ENT_QUOTES, 'UTF-8') ?></span>
                    <i class="fas fa-chevron-down" id="topbarChevron"></i>
                </button>
                <div class="topbar-dropdown" id="topbarDropdown">
                    <div class="td-header">
                        <div class="td-avatar"><?= htmlspecialchars($navIniciales, ENT_QUOTES, 'UTF-8') ?></div>
                        <div>
                            <p class="td-name"><?= htmlspecialchars($navNombreCompleto, ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="td-email"><?= htmlspecialchars($navUsuario['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <span class="td-badge"><?= htmlspecialchars($navRolLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                    <div class="td-divider"></div>
                    <ul class="td-menu">
                        <li><a href="<?= BASE_URL ?>Perfil" class="td-item"><i class="fas fa-user-circle"></i> Mi perfil</a></li>
                        <li><a href="<?= BASE_URL ?>Configuracion" class="td-item"><i class="fas fa-cog"></i> Configuración</a></li>
                        <li><a href="<?= BASE_URL ?>Ayuda" class="td-item"><i class="fas fa-question-circle"></i> Ayuda</a></li>
                    </ul>
                    <div class="td-divider"></div>
                    <button class="td-logout" id="topbarLogout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
                </div>
            </div>
        </div>
    </header>