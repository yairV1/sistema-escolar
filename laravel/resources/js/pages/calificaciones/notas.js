import { toast } from '../../components/alerts/toast';

const form = document.getElementById('formNotas');

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const btn = document.getElementById('btnGuardarNotas');
        btn.disabled = true;
        btn.classList.add('btn-loading');

        try {
            const notas = Array.from(form.querySelectorAll('[data-id-estudiante]')).map((input) => ({
                id_estudiante: input.dataset.idEstudiante,
                nota: input.value === '' ? null : input.value,
            }));

            const { data } = await window.axios.post(form.dataset.url, { notas });

            toast.success(data.message);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422) {
                toast.error('Revisá que las notas estén entre 0.0 y 5.0.');
            } else {
                toast.error('No se pudieron guardar las notas. Intenta de nuevo.');
            }
        } finally {
            btn.disabled = false;
            btn.classList.remove('btn-loading');
        }
    });
}
