import { initAutosubmit } from '../../components/listActions';
import { toast } from '../../components/alerts/toast';
import { confirmAction } from '../../components/alerts/sweetAlert';

initAutosubmit();

async function generar(url, idCurso, idPeriodo) {
    const result = await confirmAction({
        title: '¿Generar/actualizar boletines?',
        text: 'Se recalcularán las notas definitivas y el promedio de cada estudiante de este curso.',
        icon: 'question',
        confirmText: 'Generar',
    });
    if (!result.isConfirmed) return;

    try {
        await window.axios.post(url, { id_curso: idCurso, id_periodo: idPeriodo });
        toast.success('Boletines generados correctamente.');
        setTimeout(() => window.location.reload(), 900);
    } catch (error) {
        toast.error('No se pudieron generar los boletines. Intenta de nuevo.');
    }
}

document.querySelectorAll('#btnGenerarBoletines, #btnGenerarBoletinesVacio').forEach((btn) => {
    btn.addEventListener('click', () => generar(btn.dataset.url, btn.dataset.curso, btn.dataset.periodo));
});

document.querySelectorAll('[data-boletin-accion]').forEach((btn) => {
    btn.addEventListener('click', async () => {
        try {
            const { data } = await window.axios.post(btn.dataset.url);
            toast.success(data.message);
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            toast.error('No se pudo actualizar el estado del boletín.');
        }
    });
});
