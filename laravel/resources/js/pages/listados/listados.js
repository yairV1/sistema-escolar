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

function initDesactivar() {
    document.querySelectorAll('[data-desactivar]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const nombre = btn.dataset.nombre;

            const result = await confirmAction({
                title: `¿Desactivar a ${nombre}?`,
                text: 'Podrás reactivarlo más adelante desde el registro correspondiente.',
                icon: 'warning',
                confirmText: 'Desactivar',
            });
            if (!result.isConfirmed) return;

            try {
                await window.axios.post(btn.dataset.url);
                toast.success(`${nombre} fue desactivado correctamente.`);
                setTimeout(() => window.location.reload(), 900);
            } catch (error) {
                toast.error('No se pudo desactivar. Intenta de nuevo.');
            }
        });
    });
}

initAutosubmit();
initDesactivar();
