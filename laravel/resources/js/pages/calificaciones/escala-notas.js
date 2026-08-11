import { toast } from '../../components/alerts/toast';

const form = document.getElementById('formEscalaNotas');
const tabla = document.getElementById('tablaEscalaNotas')?.querySelector('tbody');
const plantilla = document.getElementById('plantillaFilaEscala');
const btnAgregar = document.getElementById('btnAgregarRangoEscala');

if (btnAgregar && tabla && plantilla) {
    btnAgregar.addEventListener('click', () => {
        tabla.appendChild(plantilla.content.cloneNode(true));
    });
}

if (tabla) {
    tabla.addEventListener('click', (event) => {
        const boton = event.target.closest('[data-eliminar-fila]');
        if (boton) {
            boton.closest('[data-fila-escala]')?.remove();
        }
    });
}

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const btn = document.getElementById('btnGuardarEscalaNotas');
        btn.disabled = true;
        btn.classList.add('btn-loading');

        try {
            const escalas = Array.from(form.querySelectorAll('[data-fila-escala]')).map((fila) => ({
                id_escala: fila.querySelector('[name="id_escala"]').value || null,
                etiqueta: fila.querySelector('[name="etiqueta"]').value,
                sigla: fila.querySelector('[name="sigla"]').value,
                valor_min: fila.querySelector('[name="valor_min"]').value,
                valor_max: fila.querySelector('[name="valor_max"]').value,
                orden: fila.querySelector('[name="orden"]').value,
            }));

            const { data } = await window.axios.post(form.dataset.url, { escalas });

            toast.success(data.message);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422) {
                toast.error('Revisá los rangos: la nota máxima debe ser mayor o igual a la mínima.');
            } else {
                toast.error('No se pudo guardar la escala de notas. Intenta de nuevo.');
            }
        } finally {
            btn.disabled = false;
            btn.classList.remove('btn-loading');
        }
    });
}
