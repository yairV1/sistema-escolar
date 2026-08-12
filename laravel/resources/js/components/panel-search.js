/**
 * Buscador de la barra superior del panel (solo existe en layouts/rector.blade.php
 * hoy — el resto de layouts no tiene .rc-search, así que esto no hace nada ahí,
 * mismo patrón defensivo que initSidebar()/initNotificacionesBell()).
 * Resultados en vivo con debounce, agrupados por tipo (estudiantes/docentes/cursos).
 */
export function initPanelSearch() {
    const wrapper = document.querySelector('.rc-search');
    const input = wrapper?.querySelector('input');
    const buscarUrl = wrapper?.dataset.buscarUrl;

    if (!wrapper || !input || !buscarUrl) return;

    const panel = document.createElement('div');
    panel.className = 'rc-search-results';
    wrapper.appendChild(panel);

    let debounceTimer = null;
    let controller = null;

    function cerrar() {
        panel.classList.remove('open');
        panel.innerHTML = '';
    }

    function renderGrupo(titulo, items) {
        if (!items.length) return '';

        return `
            <div class="rc-search-group">
                <div class="rc-search-group-title">${titulo}</div>
                ${items.map((item) => `
                    <a href="${item.url}" class="rc-search-item">
                        <span class="rc-search-item-titulo">${escaparHtml(item.titulo)}</span>
                        <span class="rc-search-item-sub">${escaparHtml(item.subtitulo ?? '')}</span>
                    </a>
                `).join('')}
            </div>
        `;
    }

    function buscar(termino) {
        controller?.abort();
        controller = new AbortController();

        window.axios.get(buscarUrl, { params: { q: termino }, signal: controller.signal })
            .then(({ data }) => {
                const grupos = [
                    renderGrupo('Estudiantes', data.estudiantes || []),
                    renderGrupo('Docentes', data.docentes || []),
                    renderGrupo('Cursos', data.cursos || []),
                ].filter(Boolean).join('');

                panel.innerHTML = grupos || `<div class="rc-search-empty">Sin resultados para "${escaparHtml(termino)}"</div>`;
                panel.classList.add('open');
            })
            .catch((err) => {
                if (err.name !== 'CanceledError') cerrar();
            });
    }

    input.addEventListener('input', () => {
        const termino = input.value.trim();
        clearTimeout(debounceTimer);

        if (termino.length < 2) {
            cerrar();
            return;
        }

        debounceTimer = setTimeout(() => buscar(termino), 250);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && panel.innerHTML) {
            panel.classList.add('open');
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.rc-search')) cerrar();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrar();
            input.blur();
        }
    });
}

function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
