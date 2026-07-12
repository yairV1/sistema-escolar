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
                'resources/js/pages/panel/panel.js',
                'resources/js/pages/listados/listados.js',
                'resources/js/pages/matriculas/matriculas.js',
                'resources/js/pages/gestion-academica/gestion-academica.js',
                'resources/js/pages/registro/estudiantes.js',
                'resources/js/pages/registro/docentes.js',
                'resources/js/pages/registro/administrativos.js',
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
