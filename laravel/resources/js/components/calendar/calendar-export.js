import { toast } from '../alerts/toast';

/** Botón "Suscribirme": pide (o genera perezosamente) el token del usuario y copia la URL webcal:// al portapapeles. */
export function initExportarCalendario() {
    const boton = document.querySelector('[data-calendario-suscribirse]');
    if (!boton) return;

    boton.addEventListener('click', async () => {
        try {
            const { data } = await window.axios.post(boton.dataset.tokenUrl);
            const urlSuscripcion = data.url.replace(/^https?:\/\//, 'webcal://');

            await navigator.clipboard.writeText(urlSuscripcion);
            toast.success('Enlace de suscripción copiado. Pégalo en Google Calendar/Outlook como "Agregar por URL".');
        } catch {
            toast.error('No se pudo generar el enlace de suscripción.');
        }
    });
}
