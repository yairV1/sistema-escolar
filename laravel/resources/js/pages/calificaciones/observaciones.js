import { toast } from '../../components/alerts/toast';

const form = document.getElementById('formObservaciones');

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const btn = document.getElementById('btnGuardarObservaciones');
        btn.disabled = true;
        btn.classList.add('btn-loading');

        try {
            const idPeriodo = form.querySelector('[name="id_periodo"]').value;

            const filas = new Map();
            form.querySelectorAll('[data-id-estudiante]').forEach((campo) => {
                const idEstudiante = campo.dataset.idEstudiante;
                const fila = filas.get(idEstudiante) || { id_estudiante: idEstudiante };
                fila[campo.dataset.campo] = campo.value === '' ? null : campo.value;
                filas.set(idEstudiante, fila);
            });

            const { data } = await window.axios.post(form.dataset.url, {
                id_periodo: idPeriodo,
                observaciones: Array.from(filas.values()),
            });

            toast.success(data.message);
        } catch (error) {
            toast.error('No se pudieron guardar las observaciones. Intenta de nuevo.');
        } finally {
            btn.disabled = false;
            btn.classList.remove('btn-loading');
        }
    });
}
