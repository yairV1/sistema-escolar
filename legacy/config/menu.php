<?php
/**
 * =====================================================
 * CONFIGURACIÓN DEL SIDEBAR
 * =====================================================
 * Única fuente de verdad para la navegación del panel.
 *
 * Para agregar un módulo nuevo: SOLO editas este archivo.
 * Nunca vuelvas a tocar el HTML del sidebar.
 *
 * Estructura soportada:
 *  - section  (título de grupo, ej: "COMUNIDAD")
 *  - items[]  (módulos dentro de la sección)
 *      - title    string
 *      - icon     string (clase Font Awesome, ej: "fas fa-users")
 *      - url      string|null  -> si el item NO tiene children, es un link directo
 *      - page     string       -> identificador para marcar el estado activo (data-page)
 *      - badge    array|null   -> ['text' => '3', 'type' => 'urgente']
 *      - roles    array|null  -> roles permitidos, ej: ['admin','rector']. null = todos
 *      - children[] (submenú tipo accordion, misma forma que items pero sin children anidados)
 *
 * Tipos de badge disponibles: 'info' (verde), 'pendiente' (naranja),
 * 'urgente' (rojo), 'novedad' (azul).
 * =====================================================
 */

return [

    // ---------- DASHBOARD (fuera de secciones, siempre visible arriba) ----------
    'top' => [
        [
            'title' => 'Inicio',
            'icon'  => 'fas fa-chart-pie',
            'url'   => BASE_URL . 'inicio',
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
                        ['title' => 'Registro de Estudiantes',     'url' => BASE_URL . 'RegistroEstudiantes', 'page' => 'RegistroEstudiantes', 'badge' => ['text' => '1,240', 'type' => 'info']],
                        ['title' => 'Registro de Docentes',        'url' => BASE_URL . 'RegistroDocentes',    'page' => 'RegistroDocentes',    'badge' => ['text' => '87', 'type' => 'info']],
                        ['title' => 'Registro de Administrativos', 'url' => BASE_URL . 'RegistroAdministrativos',       'page' => 'RegistroAdministrativos', 'badge' => ['text' => '12', 'type' => 'info']],
                    ],
                ],
                [
                    'title' => 'Matrículas',
                    'icon'  => 'fas fa-file-signature',
                    'page'  => 'matriculas',
                    'badge' => ['text' => '12', 'type' => 'pendiente'],
                    'children' => [
                        ['title' => 'Nueva matrícula', 'url' => BASE_URL . 'Matriculas/nueva',     'page' => 'MatriculasNueva'],
                        ['title' => 'Consultar',       'url' => BASE_URL . 'Matriculas',           'page' => 'Matriculas'],
                        ['title' => 'Historial',       'url' => BASE_URL . 'Matriculas/historial', 'page' => 'MatriculasHistorial'],
                    ],
                ],
                [
                    'title' => 'Listados',
                    'icon'  => 'fas fa-list-alt',
                    'url'   => BASE_URL . 'Listados',
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
                    'page'  => 'gestion-academica',
                    'children' => [
                        ['title' => 'Cursos',      'url' => BASE_URL . 'Cursos',      'page' => 'Cursos'],
                        ['title' => 'Grados',      'url' => BASE_URL . 'Grados',      'page' => 'Grados'],
                        ['title' => 'Asignaturas', 'url' => BASE_URL . 'Asignaturas', 'page' => 'Asignaturas'],
                        ['title' => 'Horarios',    'url' => BASE_URL . 'Horarios',    'page' => 'Horarios'],
                    ],
                ],
                [
                    'title' => 'Evaluaciones',
                    'icon'  => 'fas fa-clipboard-check',
                    'page'  => 'evaluaciones',
                    'children' => [
                        ['title' => 'Calificaciones', 'url' => BASE_URL . 'Calificaciones', 'page' => 'Calificaciones'],
                        ['title' => 'Observaciones',  'url' => BASE_URL . 'Observaciones',  'page' => 'Observaciones'],
                        ['title' => 'Boletines',      'url' => BASE_URL . 'Reportes',       'page' => 'Reportes', 'badge' => ['text' => 'Nuevo', 'type' => 'novedad']],
                    ],
                ],
                [
                    'title' => 'Estadísticas',
                    'icon'  => 'fas fa-chart-bar',
                    'page'  => 'estadisticas',
                    'children' => [
                        ['title' => 'Dashboard', 'url' => BASE_URL . 'Estadisticas',         'page' => 'Estadisticas'],
                        ['title' => 'Reportes',  'url' => BASE_URL . 'Estadisticas/reportes', 'page' => 'EstadisticasReportes'],
                    ],
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
                        ['title' => 'Comunicados',    'url' => BASE_URL . 'Comunicados',     'page' => 'Comunicados', 'badge' => ['text' => '3', 'type' => 'urgente']],
                        ['title' => 'Notificaciones', 'url' => BASE_URL . 'Notificaciones',  'page' => 'Notificaciones'],
                        ['title' => 'Eventos',        'url' => BASE_URL . 'Eventos',         'page' => 'Eventos'],
                        ['title' => 'Editar Landing Page', 'url' => BASE_URL . 'EditarLanding', 'page' => 'EditarLanding'],
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
                    'page'  => 'sistema',
                    'children' => [
                        ['title' => 'Usuarios',     'url' => BASE_URL . 'Usuarios',     'page' => 'UsuariosSistema'],
                        ['title' => 'Roles',        'url' => BASE_URL . 'Roles',        'page' => 'Roles'],
                        ['title' => 'Permisos',     'url' => BASE_URL . 'Permisos',     'page' => 'Permisos'],
                        ['title' => 'Auditoría',    'url' => BASE_URL . 'Auditoria',    'page' => 'Auditoria'],
                        ['title' => 'Configuración','url' => BASE_URL . 'Configuracion','page' => 'Configuracion'],
                        ['title' => 'Landing Page', 'url' => BASE_URL . 'EditarLanding','page' => 'nav'],
                    ],
                ],
            ],
        ],

    ],
];