import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

// Debe coincidir con la duración de la animación cs-toast-progress
// definida en resources/css/components/_alerts.scss
const DURATION_MS = 4000;

// Ventana de "Deshacer" (drag&drop, Fase 3) — decisión de alcance: un solo
// nivel, no un stack de undo/redo real.
const DURATION_ACCION_MS = 7000;

function show(text, variant) {
    Toastify({
        text,
        duration: DURATION_MS,
        gravity: 'top',
        position: 'right',
        close: true,
        stopOnFocus: true,
        escapeMarkup: true,
        className: `cs-toast-${variant}`,
    }).showToast();
}

export const toast = {
    success: (text) => show(text, 'success'),
    error: (text) => show(text, 'error'),
    info: (text) => show(text, 'info'),
    /** Toast clicable con una acción (ej. "Deshacer" tras mover un evento). */
    accion: (texto, { etiqueta, onClick }) => {
        Toastify({
            text: `${texto} (clic para ${etiqueta.toLowerCase()})`,
            duration: DURATION_ACCION_MS,
            gravity: 'top',
            position: 'right',
            close: true,
            stopOnFocus: true,
            escapeMarkup: true,
            className: 'cs-toast-info cs-toast-accion',
            onClick,
        }).showToast();
    },
};
