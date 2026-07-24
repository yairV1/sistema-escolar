<?php

/**
 * =====================================================
 * CONFIGURACIÓN DEL SIDEBAR (panel administrativo)
 * =====================================================
 * Única fuente de verdad para la navegación del panel. Cada item apunta
 * a una `route` de Laravel, resuelta por App\Shared\SidebarBuilder.
 *
 * Estructura soportada: top, sections > items > children, badge, roles.
 * Ver App\Shared\SidebarBuilder.
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
            'roles' => ['admin', 'rector'],
        ],
        [
            'title' => 'Mis cursos',
            'icon'  => 'fas fa-chalkboard-teacher',
            'route' => 'docente.dashboard',
            'page'  => 'DocenteDashboard',
            'roles' => ['docente'],
        ],
        [
            'title' => 'Calendario',
            'icon'  => 'fas fa-calendar-alt',
            'route' => 'calendario.index',
            'page'  => 'Calendario',
        ],
    ],

    // ---------- SECCIONES ----------
    'sections' => [

        [
            'section' => 'Registros',
            'roles' => ['admin', 'rector'],
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
            'roles' => ['admin', 'rector'],
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
                        ['title' => 'Calificaciones', 'route' => 'calificaciones.index', 'page' => 'Calificaciones'],
                        ['title' => 'Observaciones',  'route' => 'observaciones.index',  'page' => 'Observaciones'],
                        ['title' => 'Boletines',      'route' => 'boletines.index',      'page' => 'Boletines'],
                    ],
                ],
                [
                    'title' => 'Estadísticas',
                    'icon'  => 'fas fa-chart-bar',
                    'route' => 'estadisticas',
                    'page'  => 'Estadisticas',
                ],
            ],
        ],

        [
            'section' => 'Comunicación',
            'roles' => ['admin', 'rector'],
            'items' => [
                [
                    'title' => 'Comunicados',
                    'icon'  => 'fas fa-bullhorn',
                    'page'  => 'comunicacion',
                    'children' => [
                        ['title' => 'Comunicados',         'route' => 'comunicados.index', 'page' => 'Comunicados'],
                        // ['title' => 'Notificaciones', 'route' => 'notificaciones.index', 'page' => 'Notificaciones'],
                    ],
                ],
            ],
        ],

        [
            'section' => 'Ajustes',
            'roles' => ['admin', 'rector'],
            'items' => [
                [
                    'title' => 'Ajustes del Sistema',
                    'icon'  => 'fas fa-cogs',
                    'page'  => 'ajustes',
                    'children' => [
                        ['title' => 'Editar Landing Page', 'route' => 'editar-landing.index', 'page' => 'EditarLanding'],
                        ['title' => 'Configuración del Colegio', 'route' => 'configuracion-colegio.index', 'page' => 'ConfiguracionColegio', 'roles' => ['admin', 'rector']],
                        ['title' => 'Roles', 'route' => 'roles.index', 'page' => 'Roles', 'roles' => ['admin', 'rector']],
                    ],
                ],
            ],
        ],


    ],
];
