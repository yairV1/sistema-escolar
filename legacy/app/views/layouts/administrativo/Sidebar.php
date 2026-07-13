<?php
/**
 * =====================================================
 * VISTA: SIDEBAR
 * =====================================================
 * Esta vista NUNCA debe modificarse para agregar módulos.
 * Toda la data vive en config/menu.php (raíz del proyecto).
 *
 * Variables esperadas desde el controlador:
 *   $currentPage  (string) -> ej: 'RegistroEstudiantes'
 *   $userRoles    (array)  -> ej: ['admin'] o [] si no aplica
 * =====================================================
 */

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../helpers/Panel/SidebarBuilder.php';

$menuConfig = require __DIR__ . '/../../../../config/menu.php';
$currentPage = $currentPage ?? '';
// Si la vista no fija $userRoles explícitamente, se toma del usuario autenticado.
$userRoles   = $userRoles ?? Auth::roles();

$sidebarBuilder = new SidebarBuilder($menuConfig, $currentPage, $userRoles);

// Identidad del usuario autenticado, para el pie del sidebar (perfil + cerrar sesión).
$sbUsuario = Auth::usuario();
$sbNombreCompleto = $sbUsuario ? trim($sbUsuario['nombres'] . ' ' . $sbUsuario['apellidos']) : 'Invitado';
$sbIniciales = '';
foreach (explode(' ', $sbNombreCompleto) as $sbParte) {
    if ($sbParte !== '') {
        $sbIniciales .= mb_strtoupper(mb_substr($sbParte, 0, 1));
    }
}
$sbIniciales = mb_substr($sbIniciales, 0, 2) ?: '?';
$sbRolLabels = [
    'admin'  => 'Administrador',
    'rector' => 'Directivo',
];
$sbRolLabel = $sbRolLabels[$sbUsuario['rol'] ?? ''] ?? 'Panel';
?>
<script>window.BASE_URL_JS = "<?= BASE_URL ?>";</script>

<!-- Botón de menú móvil: vive fuera del <aside> para seguir visible
     cuando el sidebar está fuera de pantalla (ver Sidebar.css / Sidebar.js) -->
<button class="sidebar-mobile-trigger" id="topbarMenu" aria-label="Abrir menú">
    <i class="fas fa-bars"></i>
</button>

<!-- =====================================================
     SIDEBAR
     ===================================================== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="sidebar-brand">
            <span class="sb-name">San <strong>Cristóbal</strong></span>
            <span class="sb-sub">Panel Rector</span>
        </div>
        <button class="sidebar-collapse" id="sidebarCollapse" title="Contraer" aria-label="Contraer menú">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <nav class="sidebar-nav" aria-label="Navegación principal">
        <?= $sidebarBuilder->render() ?>
    </nav>

    <!-- Perfil + cerrar sesión (antes vivía en el topbar) -->
    <div class="sidebar-footer">
        <div class="td-header">
            <div class="td-avatar"><?= htmlspecialchars($sbIniciales, ENT_QUOTES, 'UTF-8') ?></div>
            <div>
                <p class="td-name"><?= htmlspecialchars($sbNombreCompleto, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="td-email"><?= htmlspecialchars($sbUsuario['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <span class="td-badge"><?= htmlspecialchars($sbRolLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
        <div class="td-divider"></div>
        <ul class="td-menu">
            <li><a href="<?= BASE_URL ?>Perfil" class="td-item"><i class="fas fa-user-circle"></i> Mi perfil</a></li>
        </ul>
        <div class="td-divider"></div>
        <button class="td-logout" id="topbarLogout" title="Cerrar sesión"><i class="fas fa-sign-out-alt"></i> <span>Cerrar sesión</span></button>
    </div>
</aside>

<!-- Overlay para móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>