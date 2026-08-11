document.querySelectorAll('[data-marcar-leido]').forEach((item) => {
    item.addEventListener('click', async () => {
        try {
            await window.axios.post(item.dataset.url);
            item.classList.remove('bg-body-tertiary');
            item.querySelector('.bg-primary.rounded-circle')?.remove();
            item.removeAttribute('data-marcar-leido');
        } catch (error) {
            // Silencioso: no marcar como leído no bloquea al usuario de seguir viendo el comunicado.
        }
    });
});
