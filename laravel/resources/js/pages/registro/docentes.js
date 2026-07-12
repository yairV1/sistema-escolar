import { initWizard } from '../../components/wizard';
import { toast } from '../../components/alerts/toast';
import { setFieldError, clearFormErrors, applyServerErrors } from '../../components/forms/validation';
import { appUrl } from '../../core/csrf';

const REQUERIDOS_POR_PASO = {
    1: ['primer_nombre', 'primer_apellido', 'tipo_documento', 'numero_documento', 'correo'],
    2: [],
};

const form = document.getElementById('wizardForm');
if (form) {
    function validarPaso(paso) {
        let valido = true;
        (REQUERIDOS_POR_PASO[paso] || []).forEach((campo) => {
            const input = form.querySelector(`[name="${campo}"]`);
            if (input && !input.value.trim()) {
                setFieldError(input, 'Este campo es obligatorio.');
                valido = false;
            }
        });
        return valido;
    }

    const wizard = initWizard(form, { onValidateStep: validarPaso });

    form.querySelectorAll('input, select').forEach((el) => {
        el.addEventListener('input', () => el.classList.remove('is-invalid'));
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.classList.add('btn-loading');

        try {
            const payload = Object.fromEntries(new FormData(form).entries());
            const { data } = await window.axios.post('/registro/docentes', payload);

            toast.success(`${data.message} Código: ${data.codigo}`);
            setTimeout(() => {
                window.location.href = appUrl('/listados?tab=docentes');
            }, 1500);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
                wizard.goToStep(1);
                toast.error('Revisa los campos marcados.');
            } else if (response?.status === 409) {
                toast.error(response.data.message);
            } else {
                toast.error('No se pudo guardar. Intenta de nuevo.');
            }
        } finally {
            btn.disabled = false;
            btn.classList.remove('btn-loading');
        }
    });
}
