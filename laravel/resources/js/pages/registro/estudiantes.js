import { initWizard } from '../../components/wizard';
import { toast } from '../../components/alerts/toast';
import { setFieldError, clearFormErrors, applyServerErrors } from '../../components/forms/validation';
import { initDateMask } from '../../components/forms/dateMask';
import { appUrl } from '../../core/csrf';

const CAMPO_A_PASO = {
    primer_nombre: 1, segundo_nombre: 1, primer_apellido: 1, segundo_apellido: 1,
    tipo_documento: 1, numero_documento: 1, fecha_nacimiento: 1, genero: 1,
    direccion: 2, barrio: 2, localidad: 2, ciudad: 2, estrato: 2,
    telefono_estudiante: 2, correo_estudiante: 2,
    tipo_matricula: 3, anio_lectivo: 3, grado: 3, jornada: 3, nee_descripcion: 3,
    acudiente_nombres: 4, acudiente_parentesco: 4, acudiente_tipo_documento: 4,
    acudiente_numero_documento: 4, acudiente_telefono: 4, acudiente_correo: 4,
};

const REQUERIDOS_POR_PASO = {
    1: ['primer_nombre', 'primer_apellido', 'tipo_documento', 'numero_documento', 'fecha_nacimiento', 'genero'],
    2: ['direccion', 'localidad', 'ciudad'],
    3: ['tipo_matricula', 'anio_lectivo', 'grado', 'jornada'],
    4: [],
};

const form = document.getElementById('wizardForm');
if (form) {
    const neeSi = document.getElementById('nee_si');
    const neeNo = document.getElementById('nee_no');
    const neeDescWrap = document.getElementById('neeDescWrap');
    const neeDescInput = document.getElementById('nee_descripcion');

    function syncNee() {
        const activo = neeSi.checked;
        neeDescWrap.classList.toggle('d-none', !activo);
        neeDescInput.required = activo;
    }
    neeSi.addEventListener('change', syncNee);
    neeNo.addEventListener('change', syncNee);
    syncNee();

    function validarPaso(paso) {
        let valido = true;
        (REQUERIDOS_POR_PASO[paso] || []).forEach((campo) => {
            const input = form.querySelector(`[name="${campo}"]`);
            if (input && !input.value.trim()) {
                setFieldError(input, 'Este campo es obligatorio.');
                valido = false;
            }
        });
        if (paso === 3 && neeSi.checked && !neeDescInput.value.trim()) {
            setFieldError(neeDescInput, 'Describe la necesidad educativa especial.');
            valido = false;
        }
        return valido;
    }

    initDateMask();

    const wizard = initWizard(form, { onValidateStep: validarPaso });

    form.querySelectorAll('input, select, textarea').forEach((el) => {
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
            const { data } = await window.axios.post(window.__RUTA_GUARDAR__, payload);

            toast.success(`${data.message} Código: ${data.codigo}`);
            setTimeout(() => {
                window.location.href = appUrl('/listados?tab=estudiantes');
            }, 1500);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
                const primerCampo = Object.keys(response.data.errors)[0];
                wizard.goToStep(CAMPO_A_PASO[primerCampo] || 1);
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
