<?php

/**
 * =====================================================
 * CONFIGURACIÓN DEL SIDEBAR (panel administrativo)
 * =====================================================
 * Única fuente de verdad para la navegación del panel, migrada desde
 * el config/menu.php del sistema legacy (misma estructura y contenido).
 *
 * Cada item apunta o a una `route` de Laravel (módulo ya migrado) o a
 * un path `legacy` (módulo que todavía vive en el sistema PHP plano,
 * resuelto por App\Support\SidebarBuilder contra config('legacy.url')).
 * Así el sidebar nunca tiene links rotos mientras dura la migración
 * incremental: apenas un módulo se migra, solo hace falta agregarle
 * `route` aquí (y quitar/ignorar `legacy`).
 *
 * Estructura soportada: igual que el legacy (top, sections > items >
 * children, badge, roles). Ver App\Support\SidebarBuilder.
 * =====================================================
 */

return [

    // ---------- DASHBOARD (fuera de secciones, siempre visible arriba) ----------
    'top' => [
        [
            'title' => 'Inicio',
            'icon'  => 'fas fa-chart-pie',
            'route' => 'inicio',
            'page'  => 'inicio',
        ],
    ],

    // ---------- SECCIONES ----------
    'sections' => [

        [
            'section' => 'Registros',
            'items' => [
                [
                    'title' => 'Usuarios',
                    'icon'  => 'fas fa-users',
                    'page'  => 'usuarios',
                    'children' => [
                        ['title' => 'Registro de Estudiantes',     'route' => 'registro.estudiantes.create',     'page' => 'RegistroEstudiantes'],
                        ['title' => 'Registro de Docentes',        'route' => 'registro.docentes.create',        'page' => 'RegistroDocentes'],
                        ['title' => 'Registro de Administrativos', 'route' => 'registro.administrativos.create', 'page' => 'RegistroAdministrativos'],
                    ],
                ],
                [
                    'title' => 'Matrículas',
                    'icon'  => 'fas fa-file-signature',
                    'page'  => 'matriculas',
                    'children' => [
                        ['title' => 'Consultar', 'route' => 'matriculas', 'page' => 'Matriculas'],
                    ],
                ],
                [
                    'title' => 'Listados',
                    'icon'  => 'fas fa-list-alt',
                    'route' => 'listados',
                    'page'  => 'Listados',
                ],
            ],
        ],

        [
            'section' => 'Académico',
            'items' => [
                [
                    'title' => 'Gestión Académica',
                    'icon'  => 'fas fa-graduation-cap',
                    'route' => 'gestion-academica.index',
                    'page'  => 'GestionAcademica',
                ],
                [
                    'title' => 'Evaluaciones',
                    'icon'  => 'fas fa-clipboard-check',
                    'page'  => 'evaluaciones',
                    'children' => [
                        ['title' => 'Observaciones', 'legacy' => 'Observaciones', 'page' => 'Observaciones'],
                        ['title' => 'Boletines',     'legacy' => 'Reportes',      'page' => 'Reportes'],
                    ],
                ],
                [
                    'title' => 'Estadísticas',
                    'icon'  => 'fas fa-chart-bar',
                    'legacy' => 'Estadisticas',
                    'page'  => 'Estadisticas',
                ],
            ],
        ],

        [
            'section' => 'Comunicación',
            'items' => [
                [
                    'title' => 'Comunicados',
                    'icon'  => 'fas fa-bullhorn',
                    'page'  => 'comunicacion',
                    'children' => [
                        ['title' => 'Comunicados',         'legacy' => 'Comunicados',   'page' => 'Comunicados'],
                        ['title' => 'Editar Landing Page', 'legacy' => 'EditarLanding', 'page' => 'EditarLanding'],
                    ],
                ],
            ],
        ],

        [
            'section' => 'Administración',
            // 'roles' a nivel de sección oculta TODA la sección si el usuario no cumple
            'roles' => ['admin', 'rector'],
            'items' => [
                [
                    'title' => 'Sistema',
                    'icon'  => 'fas fa-cogs',
                    'legacy' => 'EditarLanding',
                    'page'  => 'sistema',
                ],
            ],
        ],

    ],
];
