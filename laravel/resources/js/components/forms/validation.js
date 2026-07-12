/**
 * Helpers genéricos de validación en tiempo real, reutilizables por
 * cualquier formulario del sistema (no específicos de auth).
 *
 * Contrato esperado en el HTML: cada <input> lleva
 * data-feedback="idDelElementoDeError", y ese elemento tiene la
 * clase .invalid-feedback de Bootstrap.
 */

export function setFieldError(inputEl, message) {
    inputEl.classList.add('is-invalid');
    const feedbackEl = document.getElementById(inputEl.dataset.feedback);
    if (feedbackEl) {
        feedbackEl.textContent = message;
    }
}

export function clearFieldError(inputEl) {
    inputEl.classList.remove('is-invalid');
    const feedbackEl = document.getElementById(inputEl.dataset.feedback);
    if (feedbackEl) {
        feedbackEl.textContent = '';
    }
}

export function clearFormErrors(formEl) {
    formEl.querySelectorAll('.is-invalid').forEach((input) => clearFieldError(input));
}

/** Aplica errores 422 con el formato estándar de Laravel: { campo: ['mensaje', ...] } */
export function applyServerErrors(formEl, errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        const input = formEl.querySelector(`[name="${field}"]`);
        if (input) {
            setFieldError(input, messages[0]);
        }
    });
}

/** Limpia el error de un campo apenas el usuario vuelve a escribir. */
export function wireLiveValidation(inputEl) {
    inputEl.addEventListener('input', () => clearFieldError(inputEl));
}
