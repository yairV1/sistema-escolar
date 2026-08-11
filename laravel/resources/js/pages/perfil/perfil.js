import { toast } from '../../components/alerts/toast';
import { clearFormErrors, applyServerErrors, setFieldError } from '../../components/forms/validation';

// ---------- Avatar: dropzone circular con preview, click/drag&drop y quitar ----------
const avatarDropzone = document.getElementById('avatarDropzone');

if (avatarDropzone) {
    const input = document.getElementById('avatarInput');
    const preview = document.getElementById('avatarPreview');
    const initials = document.getElementById('avatarInitials');
    const removeBtn = document.getElementById('avatarRemoveBtn');
    const removedFlag = document.getElementById('avatarRemovido');

    const showPreview = (file) => {
        const reader = new FileReader();
        reader.onload = () => {
            preview.src = reader.result;
            preview.classList.remove('d-none');
            initials.classList.add('d-none');
            removeBtn.style.display = '';
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

    avatarDropzone.addEventListener('click', () => input.click());
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

    removeBtn.addEventListener('click', () => {
        input.value = '';
        preview.src = '';
        preview.classList.add('d-none');
        initials.classList.remove('d-none');
        removeBtn.style.display = 'none';
        removedFlag.value = '1';
    });
}

function wireForm(formId, buttonId, buildPayload, { resetOnSuccess = false } = {}) {
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

// FormData (no objeto plano): la foto de perfil viaja como archivo dentro
// del mismo submit, igual que el patrón de logo en configuracion-colegio.js.
wireForm('formPerfil', 'btnGuardarPerfil', (form) => new FormData(form));

wireForm('formPassword', 'btnCambiarPassword', (form) => ({
    password_actual: form.querySelector('[name="password_actual"]').value,
    password_nueva: form.querySelector('[name="password_nueva"]').value,
    password_nueva_confirmation: form.querySelector('[name="password_nueva_confirmation"]').value,
}), { resetOnSuccess: true });
