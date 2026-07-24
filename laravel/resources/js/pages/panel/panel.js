import { initSidebar } from '../../components/sidebar';
import { confirmLogout } from '../../components/alerts/sweetAlert';
import { initNotificacionesBell } from '../../components/notificaciones-bell';

initSidebar();
initNotificacionesBell();

document.querySelectorAll('[data-logout]').forEach((trigger) => {
    trigger.addEventListener('click', async (event) => {
        event.preventDefault();
        const result = await confirmLogout();
        if (result.isConfirmed) {
            trigger.closest('form')?.submit();
        }
    });
});
