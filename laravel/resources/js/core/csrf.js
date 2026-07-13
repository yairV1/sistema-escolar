/**
 * Configura los defaults de axios a partir de los <meta> del layout:
 * - app-url: base para que las rutas relativas ("/login", "/logout", ...)
 *   funcionen sin importar si la app vive en la raíz del dominio o en un
 *   subdirectorio (ej. /colegio/laravel/public).
 * - csrf-token: header X-CSRF-TOKEN en cada petición.
 */
export function initHttp() {
    if (!window.axios) {
        return;
    }

    const appUrl = document.querySelector('meta[name="app-url"]');
    if (appUrl) {
        window.axios.defaults.baseURL = appUrl.content;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
    }
}

/**
 * Construye una URL absoluta respetando el subdirectorio de la app
 * (ej. /colegio/laravel/public). Usar siempre en vez de rutas del tipo
 * `/listados` hardcodeadas, que ignoran el subdirectorio.
 */
export function appUrl(path) {
    const base = document.querySelector('meta[name="app-url"]')?.content || '';

    return base.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
}
