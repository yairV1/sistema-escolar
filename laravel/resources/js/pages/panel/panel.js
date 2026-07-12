import { initSidebar } from '../../components/sidebar';
import { confirmLogout } from '../../components/alerts/sweetAlert';

initSidebar();

document.querySelectorAll('[data-logout]').forEach((trigger) => {
    trigger.addEventListener('click', async (event) => {
        event.preventDefault();
        const result = await confirmLogout();
        if (result.isConfirmed) {
            trigger.closest('form')?.submit();
        }
    });
});
