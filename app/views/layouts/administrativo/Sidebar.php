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
$userRoles   = $userRoles ?? [];

$sidebarBuilder = new SidebarBuilder($menuConfig, $currentPage, $userRoles);
?>


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
</aside>

<!-- Overlay para móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>