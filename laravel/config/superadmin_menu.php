<?php

/**
 * =====================================================
 * SIDEBAR DEL PANEL SUPERADMIN
 * =====================================================
 * Mismo shape y mismo consumidor (App\Shared\SidebarBuilder) que
 * config/panel_menu.php, pero es un array separado: el panel SuperAdmin no
 * comparte secciones con el panel institucional (admin/rector/etc.), son
 * niveles de acceso distintos (ver docs/arquitectura/10-superadmin-plataforma.md).
 * =====================================================
 */

return [

    'top' => [
        [
            'title' => 'Panel',
            'icon' => 'fas fa-gauge-high',
            'route' => 'superadmin.dashboard',
            'page' => 'SuperAdminDashboard',
        ],
    ],

    'sections' => [
        [
            'section' => 'Plataforma',
            'items' => [
                [
                    'title' => 'Instituciones',
                    'icon' => 'fas fa-school',
                    'route' => 'superadmin.instituciones.index',
                    'page' => 'SuperAdminInstituciones',
                ],
                [
                    'title' => 'Usuarios globales',
                    'icon' => 'fas fa-users-gear',
                    'route' => 'superadmin.usuarios.index',
                    'page' => 'SuperAdminUsuarios',
                ],
                [
                    'title' => 'Roles y permisos',
                    'icon' => 'fas fa-shield-halved',
                    'route' => 'superadmin.roles.index',
                    'page' => 'SuperAdminRoles',
                ],
            ],
        ],

        [
            'section' => 'Catálogo comercial',
            'items' => [
                [
                    'title' => 'Planes',
                    'icon' => 'fas fa-layer-group',
                    'route' => 'superadmin.planes.index',
                    'page' => 'SuperAdminPlanes',
                ],
                [
                    'title' => 'Módulos',
                    'icon' => 'fas fa-puzzle-piece',
                    'route' => 'superadmin.modulos.index',
                    'page' => 'SuperAdminModulos',
                ],
            ],
        ],

        [
            'section' => 'Supervisión',
            'items' => [
                [
                    'title' => 'Auditoría',
                    'icon' => 'fas fa-clipboard-list',
                    'route' => 'superadmin.auditoria.index',
                    'page' => 'SuperAdminAuditoria',
                ],
                [
                    'title' => 'Soporte',
                    'icon' => 'fas fa-headset',
                    'route' => 'soportes.index',
                    'page' => 'SoportesIndex',
                ],
            ],
        ],

        [
            'section' => 'Ajustes',
            'items' => [
                [
                    'title' => 'Configuración global',
                    'icon' => 'fas fa-cogs',
                    'route' => 'superadmin.configuracion.index',
                    'page' => 'SuperAdminConfiguracion',
                ],
                [
                    'title' => 'Verificación en dos pasos',
                    'icon' => 'fas fa-lock',
                    'route' => 'superadmin.2fa.index',
                    'page' => 'SuperAdminDosFactores',
                ],
            ],
        ],
    ],
];
