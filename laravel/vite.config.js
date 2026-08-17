import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss',
                'resources/js/app.js',
                'resources/js/pages/auth/login.js',
                'resources/js/pages/auth/resetPassword.js',
                'resources/js/pages/auth/twoFactorChallenge.js',
                'resources/js/pages/panel/panel.js',
                'resources/js/pages/listados/listados.js',
                'resources/js/pages/matriculas/matriculas.js',
                'resources/js/pages/gestion-academica/gestion-academica.js',
                'resources/js/pages/gestion-academica/curso-detalle.js',
                'resources/js/pages/calendario/calendario.js',
                'resources/js/pages/calendario/categorias.js',
                'resources/js/pages/calificaciones/calificaciones.js',
                'resources/js/pages/calificaciones/asignacion.js',
                'resources/js/pages/calificaciones/notas.js',
                'resources/js/pages/calificaciones/planilla.js',
                'resources/js/pages/observaciones/observaciones.js',
                'resources/js/pages/boletines/boletines.js',
                'resources/js/pages/asistencia/asistencia.js',
                'resources/js/pages/comunicados/comunicados.js',
                'resources/js/pages/perfil/perfil.js',
                'resources/js/pages/editar-landing/editar-landing.js',
                'resources/js/pages/configuracion-colegio/configuracion-colegio.js',
                'resources/js/pages/roles/roles.js',
                'resources/js/pages/registro/estudiantes.js',
                'resources/js/pages/registro/docentes.js',
                'resources/js/pages/registro/administrativos.js',
                'resources/js/pages/soporte/soporte.js',
                'resources/js/pages/superadmin/dashboard.js',
                'resources/js/pages/superadmin/instituciones.js',
                'resources/js/pages/superadmin/planes.js',
                'resources/js/pages/superadmin/modulos.js',
                'resources/js/pages/superadmin/usuarios.js',
                'resources/js/pages/superadmin/roles.js',
                'resources/js/pages/superadmin/auditoria.js',
                'resources/js/pages/superadmin/configuracion.js',
                'resources/js/pages/superadmin/dosfactores.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
