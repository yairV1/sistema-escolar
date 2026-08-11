import { toast } from '../../components/alerts/toast';
import { confirmAction } from '../../components/alerts/sweetAlert';
import { initEstadoToggle } from '../../components/listActions';

/**
 * No se usa FormData nativo: un checkbox sin marcar simplemente no aparece
 * en el envío, así que un rol al que se le desmarcan TODOS los permisos
 * desaparecería del payload en vez de mandarse como lista vacía — y el
 * backend interpreta "rol ausente" como "no tocar este rol", no como
 * "vaciar sus permisos". Se arma el objeto a mano para que los 7 roles
 * viajen siempre, aunque queden en un arreglo vacío.
 */
function construirMatriz(form) {
    const matriz = {};

    form.querySelectorAll('input[type="checkbox"][name^="permisos["]').forEach((checkbox) => {
        const idRol = checkbox.name.match(/permisos\[(\d+)\]/)[1];
        matriz[idRol] ??= [];
        if (checkbox.checked) {
            matriz[idRol].push(Number(checkbox.value));
        }
    });

    return matriz;
}

function initFormMatriz() {
    const form = document.getElementById('formMatrizRoles');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const result = await confirmAction({
            title: '¿Guardar la nueva matriz de permisos?',
            text: 'Cualquier usuario cuyo rol pierda o gane permisos perderá su sesión activa.',
            icon: 'question',
            confirmText: 'Guardar',
        });
        if (!result.isConfirmed) return;

        const submitBtn = document.getElementById('btnGuardarMatriz');
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;

        try {
            const { data } = await window.axios.post(form.dataset.url, {
                permisos: construirMatriz(form),
            });
            toast.success(data.message);
        } catch (error) {
            toast.error('No se pudo guardar la matriz. Intenta de nuevo.');
        } finally {
            submitBtn.classList.remove('btn-loading');
            submitBtn.disabled = false;
        }
    });
}

initFormMatriz();
initEstadoToggle();
