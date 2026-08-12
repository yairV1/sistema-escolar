/**
 * Máscara simple para inputs de texto que reemplazan un <input type="date">
 * cuando escribir a mano es más rápido que navegar el calendario nativo
 * (ej. fecha de nacimiento). Contrato: <input data-date-mask> — inserta
 * las barras solo, sin validar que la fecha exista (eso lo hace el
 * servidor al enviar).
 */
export function initDateMask() {
    document.querySelectorAll('[data-date-mask]').forEach((input) => {
        input.addEventListener('input', () => {
            const digitos = input.value.replace(/\D/g, '').slice(0, 8);
            let formateado = digitos;
            if (digitos.length > 4) {
                formateado = `${digitos.slice(0, 2)}/${digitos.slice(2, 4)}/${digitos.slice(4)}`;
            } else if (digitos.length > 2) {
                formateado = `${digitos.slice(0, 2)}/${digitos.slice(2)}`;
            }
            input.value = formateado;
        });
    });
}
