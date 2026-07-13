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
 */
export function initTheme() {
    const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
    applyTheme(current);

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const active = document.documentElement.getAttribute('data-bs-theme');
            applyTheme(active === 'dark' ? 'light' : 'dark');
        });
    });
}
