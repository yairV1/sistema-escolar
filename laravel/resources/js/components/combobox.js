/**
 * Combobox liviano: un <input> de texto que sugiere opciones a medida que
 * se escribe (filtrado en el cliente, sin ir al servidor) y guarda el valor
 * elegido en un <input type="hidden">. Reemplaza un <select> largo cuando
 * hay muchas opciones y buscar por nombre es más rápido que scrollear una
 * lista (ver Docente/anotaciones/index.blade.php).
 *
 * Contrato esperado en el HTML:
 * <div data-combobox data-options='[{"value":1,"label":"...","sub":"..."}]'
 *      data-selected-value="1" data-selected-label="...">
 *     <input class="combobox-input" type="text" autocomplete="off">
 *     <input class="combobox-hidden" type="hidden" name="...">
 *     <div class="combobox-list"></div>
 * </div>
 *
 * data-selected-value/label son opcionales (precargan el combobox, usado
 * por los modales de edición). El resto de los atributos del <input>
 * hidden (name, data-feedback) los define la página, este componente no
 * los toca.
 */
export function initCombobox() {
    document.querySelectorAll('[data-combobox]').forEach((wrapper) => {
        const input = wrapper.querySelector('.combobox-input');
        const hidden = wrapper.querySelector('.combobox-hidden');
        const list = wrapper.querySelector('.combobox-list');
        if (!input || !hidden || !list) return;

        let options = [];
        try {
            options = JSON.parse(wrapper.dataset.options || '[]');
        } catch (error) {
            options = [];
        }

        if (wrapper.dataset.selectedValue) {
            hidden.value = wrapper.dataset.selectedValue;
            input.value = wrapper.dataset.selectedLabel || '';
        }

        function cerrar() {
            list.classList.remove('open');
            list.innerHTML = '';
        }

        function elegir(opcion) {
            hidden.value = opcion.value;
            input.value = opcion.label;
            cerrar();
        }

        function render(items) {
            if (!items.length) {
                list.innerHTML = '<div class="combobox-empty">Sin resultados.</div>';
                list.classList.add('open');
                return;
            }

            list.innerHTML = items.slice(0, 8).map((opt, i) => `
                <button type="button" class="combobox-item" data-index="${i}">
                    <span class="combobox-item-label">${escaparHtml(opt.label)}</span>
                    ${opt.sub ? `<span class="combobox-item-sub">${escaparHtml(opt.sub)}</span>` : ''}
                </button>
            `).join('');
            list.classList.add('open');
            list.dataset.currentOptions = JSON.stringify(items.slice(0, 8));
        }

        input.addEventListener('input', () => {
            hidden.value = '';
            const termino = input.value.trim().toLowerCase();
            if (!termino) {
                cerrar();
                return;
            }
            render(options.filter((opt) => opt.label.toLowerCase().includes(termino)
                || (opt.sub || '').toLowerCase().includes(termino)));
        });

        input.addEventListener('focus', () => {
            if (input.value.trim() && !hidden.value) input.dispatchEvent(new Event('input'));
        });

        list.addEventListener('click', (event) => {
            const item = event.target.closest('.combobox-item');
            if (!item) return;
            const items = JSON.parse(list.dataset.currentOptions || '[]');
            const opcion = items[Number(item.dataset.index)];
            if (opcion) elegir(opcion);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                cerrar();
                input.blur();
            }
        });

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) cerrar();
        });
    });
}

function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
