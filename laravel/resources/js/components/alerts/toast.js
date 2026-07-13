import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

// Debe coincidir con la duración de la animación cs-toast-progress
// definida en resources/css/components/_alerts.scss
const DURATION_MS = 4000;

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
};
