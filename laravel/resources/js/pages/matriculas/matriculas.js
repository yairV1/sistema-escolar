import { toast } from '../../components/alerts/toast';
import { confirmAction } from '../../components/alerts/sweetAlert';

function initAutosubmit() {
    document.querySelectorAll('[data-autosubmit-form]').forEach((form) => {
        form.querySelectorAll('[data-autosubmit]').forEach((el) => {
            el.addEventListener('change', () => form.submit());
        });

        let timer;
        form.querySelectorAll('[data-autosubmit-debounce]').forEach((input) => {
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => form.submit(), 400);
            });
        });
    });
}

function initCambiarEstado() {
    document.querySelectorAll('[data-cambiar-estado]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const { nombre, accion, estado, url } = btn.dataset;

            const result = await confirmAction({
                title: `¿${accion} la matrícula de ${nombre}?`,
                icon: 'question',
                confirmText: accion,
            });
            if (!result.isConfirmed) return;

            try {
                await window.axios.post(url, { estado });
                toast.success(`Matrícula actualizada: ${accion.toLowerCase()}.`);
                setTimeout(() => window.location.reload(), 900);
            } catch (error) {
                toast.error('No se pudo actualizar la matrícula. Intenta de nuevo.');
            }
        });
    });
}

initAutosubmit();
initCambiarEstado();
