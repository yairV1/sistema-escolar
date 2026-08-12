/**
 * Campanita de notificaciones del panel — mismo patrón toggle/click-afuera/
 * Escape que el dropup del menú de usuario (components/sidebar.js), pero
 * fuera del sidebar así que vive en su propio módulo. Se marca todo como
 * leído al abrir el panel (un solo gesto, sin botón por-notificación).
 */
export function initNotificacionesBell() {
    const raiz = document.getElementById('campanitaNotificaciones');
    if (!raiz) return;

    const toggle = document.getElementById('campanitaToggle');
    const panel = document.getElementById('campanitaPanel');
    const badge = document.getElementById('campanitaBadge');
    const lista = document.getElementById('campanitaLista');

    const indexUrl = raiz.dataset.indexUrl;
    const leerTodasUrl = raiz.dataset.leerTodasUrl;
    const calendarioUrl = raiz.dataset.calendarioUrl;

    let cargado = false;

    function cerrar() {
        panel.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    function pintar(notificaciones) {
        if (notificaciones.length === 0) {
            lista.innerHTML = '<p class="campanita-vacio">No tienes notificaciones.</p>';
            return;
        }

        lista.innerHTML = notificaciones.map((n) => `
            <a href="${n.url || calendarioUrl}" class="campanita-item ${n.leida ? 'leida' : ''}">
                <span class="campanita-item-titulo">${escaparHtml(n.titulo)}</span>
                <span class="campanita-item-meta">${escaparHtml(n.creada_hace)}</span>
            </a>
        `).join('');
    }

    function actualizarBadge(noLeidas) {
        if (noLeidas > 0) {
            badge.textContent = noLeidas > 9 ? '9+' : String(noLeidas);
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    function cargar() {
        window.axios.get(indexUrl).then((response) => {
            actualizarBadge(response.data.no_leidas);
            pintar(response.data.notificaciones);
        });
    }

    function marcarLeidas() {
        if (badge.classList.contains('d-none')) return;

        window.axios.post(leerTodasUrl).then(() => actualizarBadge(0));
    }

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const abrir = !panel.classList.contains('open');

        // Mismo motivo que el cierre simétrico en components/sidebar.js:
        // el menú de usuario también usa stopPropagation() en su propio
        // toggle, así que sin este cierre explícito ambos paneles pueden
        // quedar abiertos y superpuestos a la vez.
        document.getElementById('userMenuDropup')?.classList.remove('open');
        document.getElementById('userMenuToggle')?.setAttribute('aria-expanded', 'false');

        if (abrir) {
            panel.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
            if (!cargado) {
                cargado = true;
                cargar();
            }
            marcarLeidas();
        } else {
            cerrar();
        }
    });

    document.addEventListener('click', (e) => {
        if (panel.classList.contains('open') && !e.target.closest('#campanitaNotificaciones')) {
            cerrar();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && panel.classList.contains('open')) {
            cerrar();
        }
    });

    cargar();
}

function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
