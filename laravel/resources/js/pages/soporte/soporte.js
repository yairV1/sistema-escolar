import { initCrudForms } from '../../components/listActions';
import { toast } from '../../components/alerts/toast';
import { confirmAction } from '../../components/alerts/sweetAlert';
import { appUrl } from '../../core/csrf';

initCrudForms();

const btnResolver = document.getElementById('btnMarcarResuelto');

if (btnResolver) {
    btnResolver.addEventListener('click', async () => {
        const result = await confirmAction({
            title: '¿Marcar como resuelto?',
            text: 'El soporte pasará a la bandeja de resueltos.',
            icon: 'question',
            confirmText: 'Marcar resuelto',
        });
        if (!result.isConfirmed) return;

        btnResolver.disabled = true;
        btnResolver.classList.add('btn-loading');

        try {
            const { data } = await window.axios.post(btnResolver.dataset.url);
            toast.success(data.message);
            setTimeout(() => {
                window.location.href = appUrl('soportes');
            }, 900);
        } catch (error) {
            toast.error('No se pudo marcar como resuelto. Intenta de nuevo.');
            btnResolver.disabled = false;
            btnResolver.classList.remove('btn-loading');
        }
    });
}
