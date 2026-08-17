import { toast } from '../../components/alerts/toast';
import { initAutosubmit } from '../../components/listActions';

initAutosubmit();

const form = document.getElementById('formPlanilla');

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const btn = document.getElementById('btnGuardarPlanilla');
        btn.disabled = true;
        btn.classList.add('btn-loading');

        const porActividad = new Map();
        form.querySelectorAll('[data-id-estudiante][data-id-actividad]').forEach((input) => {
            const idActividad = input.dataset.idActividad;
            if (!porActividad.has(idActividad)) {
                const th = form.querySelector(`th[data-id-actividad="${idActividad}"]`);
                porActividad.set(idActividad, { url: th.dataset.notasUrl, titulo: th.dataset.titulo, notas: [] });
            }
            porActividad.get(idActividad).notas.push({
                id_estudiante: input.dataset.idEstudiante,
                nota: input.value === '' ? null : input.value,
            });
        });

        const resultados = await Promise.allSettled(
            Array.from(porActividad.values()).map(({ url, notas }) => window.axios.post(url, { notas }))
        );

        const fallidas = Array.from(porActividad.values()).filter((_, i) => resultados[i].status === 'rejected');

        if (fallidas.length === 0) {
            toast.success('Notas guardadas correctamente.');
        } else {
            toast.error(`No se pudieron guardar las notas de: ${fallidas.map((f) => f.titulo).join(', ')}. Revisá que estén entre 0.0 y 5.0.`);
        }

        setTimeout(() => window.location.reload(), 900);
    });
}
