import { toast } from '../../components/alerts/toast';
import { clearFormErrors, applyServerErrors, setFieldError } from '../../components/forms/validation';

// ---------- Foto de perfil: dropzone circular con preview, click/drag&drop y quitar ----------
// Mismo patrón que configuracion-colegio.js (logoDropzone), adaptado a los ids de fotoDropzone.
const avatarDropzone = document.getElementById('fotoDropzone');

if (avatarDropzone) {
    const input = document.getElementById('fotoInput');
    const preview = document.getElementById('fotoPreview');
    const placeholder = document.getElementById('fotoPlaceholder');
    const removeBtn = document.getElementById('fotoRemove');
    const removedFlag = document.getElementById('fotoRemovida');

    const showPreview = (file) => {
        const reader = new FileReader();
        reader.onload = () => {
            preview.src = reader.result;
            preview.classList.remove('d-none');
            placeholder.classList.add('d-none');
            removeBtn.classList.remove('d-none');
            avatarDropzone.classList.add('has-preview');
        };
        reader.readAsDataURL(file);
    };

    const setFile = (file) => {
        if (!file || !file.type.startsWith('image/')) return;

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        removedFlag.value = '0';
        showPreview(file);
    };

    avatarDropzone.addEventListener('click', (event) => {
        if (event.target === removeBtn || removeBtn.contains(event.target)) return;
        input.click();
    });
    avatarDropzone.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
        }
    });

    input.addEventListener('change', () => setFile(input.files[0]));

    ['dragenter', 'dragover'].forEach((eventName) => {
        avatarDropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            avatarDropzone.classList.add('drag-over');
        });
    });
    ['dragleave', 'drop'].forEach((eventName) => {
        avatarDropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            avatarDropzone.classList.remove('drag-over');
        });
    });
    avatarDropzone.addEventListener('drop', (event) => setFile(event.dataTransfer.files[0]));

    removeBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        input.value = '';
        preview.src = '';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
        removeBtn.classList.add('d-none');
        avatarDropzone.classList.remove('has-preview');
        removedFlag.value = '1';
    });
}

function wireForm(formId, buttonId, buildPayload, { resetOnSuccess = false, onSuccess } = {}) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const btn = document.getElementById(buttonId);
        btn.disabled = true;
        btn.classList.add('btn-loading');

        try {
            const { data } = await window.axios.post(form.dataset.url, buildPayload(form));
            toast.success(data.message);
            if (resetOnSuccess) form.reset();
            onSuccess?.(data);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
                toast.error('Revisa los campos marcados.');
            } else if (response?.status === 422 && response.data.message) {
                const actual = form.querySelector('[name="password_actual"]');
                if (actual) setFieldError(actual, response.data.message);
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

wireForm('formPerfil', 'btnGuardarPerfil', (form) => new FormData(form), {
    onSuccess: (data) => {
        // Refleja la foto nueva (o su ausencia) en el avatar del sidebar sin recargar la página.
        document.querySelectorAll('.td-avatar').forEach((avatar) => {
            avatar.innerHTML = data.foto_perfil_url
                ? `<img src="${data.foto_perfil_url}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">`
                : avatar.dataset.iniciales || '';
        });
    },
});

wireForm('formPassword', 'btnCambiarPassword', (form) => ({
    password_actual: form.querySelector('[name="password_actual"]').value,
    password_nueva: form.querySelector('[name="password_nueva"]').value,
    password_nueva_confirmation: form.querySelector('[name="password_nueva_confirmation"]').value,
}), { resetOnSuccess: true });
