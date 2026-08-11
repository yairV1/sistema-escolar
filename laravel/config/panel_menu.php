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
        [
            'title' => 'Inicio',
            'icon'  => 'fas fa-house',
            'route' => 'estudiante.inicio',
            'page'  => 'EstudianteInicio',
            'roles' => ['estudiante'],
        ],
        [
            'title' => 'Inicio',
            'icon'  => 'fas fa-house',
            'route' => 'acudiente.inicio',
            'page'  => 'AcudienteInicio',
            'roles' => ['acudiente'],
        ],
        [
            'title' => 'Soporte',
            'icon'  => 'fas fa-life-ring',
            'route' => 'soporte.create',
            'page'  => 'SoporteCreate',
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
                    'modulo' => 'matriculas',
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
                    'page'  => 'gestion-academica',
                    'children' => [
                        ['title' => 'Materias',     'route' => 'gestion-academica.materias.index',     'page' => 'GestionAcademicaMaterias'],
                        ['title' => 'Cursos',       'route' => 'gestion-academica.cursos.index',       'page' => 'GestionAcademicaCursos'],
                        ['title' => 'Asignaciones', 'route' => 'gestion-academica.asignaciones.index', 'page' => 'GestionAcademicaAsignaciones'],
                        ['title' => 'Horarios',     'route' => 'gestion-academica.horarios.index',     'page' => 'GestionAcademicaHorarios', 'modulo' => 'horarios'],
                    ],
                ],
                [
                    'title' => 'Evaluaciones',
                    'icon'  => 'fas fa-clipboard-check',
                    'page'  => 'evaluaciones',
                    'children' => [
                        ['title' => 'Calificaciones', 'route' => 'calificaciones.index', 'page' => 'Calificaciones'],
                        ['title' => 'Observaciones',  'route' => 'observaciones.index',  'page' => 'Observaciones'],
                        ['title' => 'Boletines',      'route' => 'boletines.index',      'page' => 'Boletines', 'modulo' => 'boletines'],
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
                    'modulo' => 'comunicados',
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
                        // ['title' => 'Roles', 'route' => 'roles.index', 'page' => 'Roles', 'roles' => ['admin', 'rector']],
                    ],
                ],
            ],
        ],

        [
            'section' => 'Soporte técnico',
            'roles' => ['admin'],
            'items' => [
                ['title' => 'Bandeja de soportes', 'icon' => 'fas fa-headset', 'route' => 'soportes.index', 'page' => 'SoportesIndex'],
            ],
        ],

        [
            'section' => 'Mi colegio',
            'roles' => ['estudiante'],
            'items' => [
                ['title' => 'Horario',     'icon' => 'fas fa-calendar-week',   'route' => 'estudiante.horario',     'page' => 'EstudianteHorario', 'modulo' => 'horarios'],
                ['title' => 'Materias',    'icon' => 'fas fa-book',            'route' => 'estudiante.materias',    'page' => 'EstudianteMaterias'],
                ['title' => 'Notas',       'icon' => 'fas fa-star',            'route' => 'estudiante.notas',       'page' => 'EstudianteNotas', 'modulo' => 'boletines'],
                ['title' => 'Asistencia',  'icon' => 'fas fa-clipboard-check', 'route' => 'estudiante.asistencia',  'page' => 'EstudianteAsistencia'],
                ['title' => 'Comunicados', 'icon' => 'fas fa-bullhorn',        'route' => 'estudiante.comunicados', 'page' => 'EstudianteComunicados', 'modulo' => 'comunicados'],
                ['title' => 'Mi perfil',   'icon' => 'fas fa-id-card',         'route' => 'estudiante.perfil',      'page' => 'EstudiantePerfil'],
            ],
        ],

        [
            'section' => 'Mi panel',
            'roles' => ['docente'],
            'items' => [
                ['title' => 'Mis estudiantes', 'icon' => 'fas fa-user-graduate',   'route' => 'docente.estudiantes',    'page' => 'DocenteEstudiantes'],
                ['title' => 'Asistencia',      'icon' => 'fas fa-clipboard-check', 'route' => 'docente.asistencia',     'page' => 'DocenteAsistencia'],
                ['title' => 'Calificaciones',  'icon' => 'fas fa-marker',          'route' => 'docente.calificaciones', 'page' => 'DocenteCalificaciones'],
                ['title' => 'Director de grupo', 'icon' => 'fas fa-user-tie',      'route' => 'director-grupo.index',   'page' => 'DocenteDirectorGrupo', 'modulo' => 'boletines'],
                ['title' => 'Horario',         'icon' => 'fas fa-calendar-week',   'route' => 'docente.horario',        'page' => 'DocenteHorario', 'modulo' => 'horarios'],
                ['title' => 'Comunicados',     'icon' => 'fas fa-bullhorn',        'route' => 'docente.comunicados',    'page' => 'DocenteComunicados', 'modulo' => 'comunicados'],
                ['title' => 'Mi perfil',       'icon' => 'fas fa-id-card',         'route' => 'perfil.show',            'page' => 'Perfil'],
            ],
        ],

        [
            'section' => 'Mis acudidos',
            'roles' => ['acudiente'],
            'items' => [
                ['title' => 'Estudiantes a cargo', 'icon' => 'fas fa-user-graduate',   'route' => 'acudiente.estudiantes', 'page' => 'AcudienteEstudiantes'],
                ['title' => 'Horario',             'icon' => 'fas fa-calendar-week',   'route' => 'acudiente.horario',     'page' => 'AcudienteHorario', 'modulo' => 'horarios'],
                ['title' => 'Notas',               'icon' => 'fas fa-star',            'route' => 'acudiente.notas',       'page' => 'AcudienteNotas', 'modulo' => 'boletines'],
                ['title' => 'Asistencia',          'icon' => 'fas fa-clipboard-check', 'route' => 'acudiente.asistencia',  'page' => 'AcudienteAsistencia'],
                ['title' => 'Comunicados',         'icon' => 'fas fa-bullhorn',        'route' => 'acudiente.comunicados', 'page' => 'AcudienteComunicados', 'modulo' => 'comunicados'],
                ['title' => 'Mi perfil',           'icon' => 'fas fa-id-card',         'route' => 'acudiente.perfil',      'page' => 'AcudientePerfil'],
            ],
        ],

    ],
];
