const STORAGE_KEY = 'cs-theme';

/**
 * persist=false para la sincronización inicial al cargar la página: ese
 * valor puede venir de la detección automática de tema del sistema
 * (prefers-color-scheme), no de una elección consciente del usuario — no
 * hay que guardar eso en la cuenta como si lo hubiera elegido. Solo se
 * persiste cuando el usuario clickea el toggle a propósito.
 */
function applyTheme(theme, { persist = true } = {}) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    localStorage.setItem(STORAGE_KEY, theme);
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.setAttribute('aria-pressed', theme === 'dark');
    });

    // Fire-and-forget: en la página de login (sin sesión) esto simplemente
    // falla en silencio, mismo patrón defensivo que initPanelSearch().
    if (persist) {
        window.axios?.post('/perfil/preferencias', { tema: theme }).catch(() => {});
    }
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
    applyTheme(current, { persist: false });

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const active = document.documentElement.getAttribute('data-bs-theme');
            applyTheme(active === 'dark' ? 'light' : 'dark');
        });
    });
}
