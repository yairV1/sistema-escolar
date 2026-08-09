const STORAGE_KEY = 'cs-theme';

function applyTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    localStorage.setItem(STORAGE_KEY, theme);
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.setAttribute('aria-pressed', theme === 'dark');
    });
}

/**
 * Se llama tras DOMContentLoaded: sincroniza el estado de los botones
 * de toggle y engancha su evento click. El atributo data-bs-theme ya
 * fue fijado antes (ver script inline en layouts/auth.blade.php) para
 * evitar el parpadeo de tema incorrecto al cargar la página.
 *
 * El SuperAdmin no tiene toggle: su tema siempre es oscuro, fijado por
 * su propio script inline (layouts/superadmin.blade.php), sin leer
 * localStorage. Si esta función corriera igual ahí, applyTheme() pisaría
 * silenciosamente el STORAGE_KEY con 'dark' cada vez que un SuperAdmin
 * visita su panel, arruinando la preferencia guardada del panel
 * institucional la próxima vez que la visite.
 */
export function initTheme() {
    if (document.body?.dataset.panel === 'superadmin') {
        return;
    }

    const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
    applyTheme(current);

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const active = document.documentElement.getAttribute('data-bs-theme');
            applyTheme(active === 'dark' ? 'light' : 'dark');
        });
    });
}
