import { initAutosubmit } from '../../components/listActions';
import { toast } from '../../components/alerts/toast';

initAutosubmit();

const form = document.getElementById('formAsistencia');

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const btn = document.getElementById('btnGuardarAsistencia');
        btn.disabled = true;
        btn.classList.add('btn-loading');

        try {
            const estudiantes = new Set(
                Array.from(form.querySelectorAll('[data-id-estudiante]')).map((el) => el.dataset.idEstudiante)
            );

            const registros = Array.from(estudiantes).map((id) => ({
                id_estudiante: id,
                estado_asistencia: form.querySelector(`[data-id-estudiante="${id}"][data-campo="estado"]`).value,
                observacion: form.querySelector(`[data-id-estudiante="${id}"][data-campo="observacion"]`).value || null,
            }));

            const { data } = await window.axios.post(form.dataset.url, {
                fecha: form.dataset.fecha,
                registros,
            });

            toast.success(data.message);
        } catch (error) {
            toast.error('No se pudo guardar la asistencia. Intenta de nuevo.');
        } finally {
            btn.disabled = false;
            btn.classList.remove('btn-loading');
        }
    });
}
