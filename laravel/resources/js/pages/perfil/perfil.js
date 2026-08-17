import { toast } from '../../components/alerts/toast';
import { confirmAction } from '../../components/alerts/sweetAlert';
import { clearFormErrors, applyServerErrors, setFieldError, clearFieldError } from '../../components/forms/validation';

// ---------- Avatar: dropzone circular con preview, click/drag&drop y quitar ----------
const avatarDropzone = document.getElementById('avatarDropzone');

if (avatarDropzone) {
    const input = document.getElementById('avatarInput');
    const preview = document.getElementById('avatarPreview');
    const initials = document.getElementById('avatarInitials');
    const removeBtn = document.getElementById('avatarRemoveBtn');
    const removedFlag = document.getElementById('avatarRemovido');
    const uploadBtn = document.getElementById('avatarUploadBtn');

    uploadBtn?.addEventListener('click', () => input.click());

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

function wireForm(formId, buttonId, buildPayload, { resetOnSuccess = false, reloadOnSuccess = false } = {}) {
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
            // El avatar/nombre del sidebar y de la topbar se renderizan del
            // lado del servidor una sola vez al cargar la página — recargar
            // acá es lo que hace que una foto o nombre nuevos se vean ahí
            // también, no solo en este formulario.
            if (reloadOnSuccess) setTimeout(() => window.location.reload(), 900);
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
wireForm('formPerfil', 'btnGuardarPerfil', (form) => new FormData(form), { reloadOnSuccess: true });

wireForm('formPassword', 'btnCambiarPassword', (form) => ({
    password_actual: form.querySelector('[name="password_actual"]').value,
    password_nueva: form.querySelector('[name="password_nueva"]').value,
    password_nueva_confirmation: form.querySelector('[name="password_nueva_confirmation"]').value,
}), { resetOnSuccess: true });

// ---------- Color de acento: "Restablecer" no borra el picker en el momento,
// solo marca la intención — el valor real (null) se manda recién al guardar,
// mismo patrón que foto_perfil_removido en el formulario de datos personales. ----------
const colorAcentoInput = document.getElementById('prefColorAcento');
const colorAcentoResetBtn = document.getElementById('prefColorReset');
const colorAcentoResetFlag = document.getElementById('colorAcentoRestablecido');

colorAcentoResetBtn?.addEventListener('click', () => {
    if (colorAcentoInput) colorAcentoInput.value = colorAcentoInput.dataset.default;
    if (colorAcentoResetFlag) colorAcentoResetFlag.value = '1';
});

colorAcentoInput?.addEventListener('input', () => {
    if (colorAcentoResetFlag) colorAcentoResetFlag.value = '0';
});

// Recarga porque un cambio de idioma, tema o color necesita repintar toda la
// página del lado del servidor, no solo este formulario.
wireForm('formPreferencias', 'btnGuardarPreferencias', (form) => ({
    idioma: form.querySelector('[name="idioma"]').value,
    tema: form.querySelector('[name="tema"]').value,
    notificaciones_email: form.querySelector('[name="notificaciones_email"]').checked,
    color_acento: colorAcentoInput ? (colorAcentoResetFlag.value === '1' ? null : colorAcentoInput.value) : undefined,
}), { reloadOnSuccess: true });

// ---------- Verificación en dos pasos (2FA) ----------
// Misma lógica que resources/js/pages/superadmin/dosfactores.js, portada acá
// porque ahora el tab "Verificación en dos pasos" vive en Mi perfil para
// todos los roles (antes solo existía la página standalone de SuperAdmin,
// que sigue igual y usa su propio bundle).
function mostrarCodigosRecuperacion(codigos) {
    const card = document.getElementById('cardCodigosRecuperacion');
    const lista = document.getElementById('listaCodigosRecuperacion');
    if (!card || !lista) return;

    lista.innerHTML = '';
    codigos.forEach((codigo) => {
        const li = document.createElement('li');
        li.className = 'list-group-item font-monospace';
        li.textContent = codigo;
        lista.appendChild(li);
    });
    card.classList.remove('d-none');
}

function initHabilitar2fa(panel) {
    const btnHabilitar = document.getElementById('btnHabilitar2fa');
    const bloqueEnrolamiento = document.getElementById('bloqueEnrolamiento');
    const qrContenedor = document.getElementById('qrContenedor');
    const secretoTexto = document.getElementById('secretoTexto');
    const formConfirmar = document.getElementById('formConfirmar2fa');
    if (!btnHabilitar) return;

    btnHabilitar.addEventListener('click', async () => {
        btnHabilitar.disabled = true;
        try {
            const { data } = await window.axios.post(panel.dataset.enableUrl);
            qrContenedor.innerHTML = data.qr_svg;
            secretoTexto.textContent = data.secreto;
            bloqueEnrolamiento.classList.remove('d-none');
            btnHabilitar.classList.add('d-none');
        } catch (error) {
            toast.error('No se pudo iniciar la activación de 2FA.');
        } finally {
            btnHabilitar.disabled = false;
        }
    });

    formConfirmar?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const codigoInput = document.getElementById('codigoConfirmar');
        clearFieldError(codigoInput);

        try {
            const { data } = await window.axios.post(panel.dataset.confirmUrl, { codigo: codigoInput.value.trim() });
            toast.success(data.message);
            mostrarCodigosRecuperacion(data.codigos_recuperacion);
            bloqueEnrolamiento.classList.add('d-none');
            setTimeout(() => window.location.reload(), 6000);
        } catch (error) {
            setFieldError(codigoInput, error.response?.data?.message || 'Código incorrecto.');
        }
    });
}

function initRegenerar2fa(panel) {
    const btn = document.getElementById('btnRegenerarCodigos');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const result = await confirmAction({
            title: '¿Regenerar códigos de recuperación?',
            text: 'Los códigos anteriores dejarán de funcionar.',
            icon: 'warning',
            confirmText: 'Regenerar',
        });
        if (!result.isConfirmed) return;

        try {
            const { data } = await window.axios.post(panel.dataset.regenerarUrl);
            mostrarCodigosRecuperacion(data.codigos_recuperacion);
        } catch (error) {
            toast.error('No se pudo regenerar los códigos.');
        }
    });
}

function initDeshabilitar2fa(panel) {
    const form = document.getElementById('formDeshabilitar2fa');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const passwordInput = document.getElementById('passwordDeshabilitar');
        clearFieldError(passwordInput);

        const result = await confirmAction({
            title: '¿Desactivar 2FA?',
            text: 'Tu cuenta quedará protegida solo con contraseña.',
            icon: 'warning',
            confirmText: 'Desactivar',
        });
        if (!result.isConfirmed) return;

        try {
            const { data } = await window.axios.post(panel.dataset.disableUrl, { password: passwordInput.value });
            toast.success(data.message);
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            const mensaje = error.response?.data?.errors?.password?.[0] || error.response?.data?.message || 'No se pudo desactivar 2FA.';
            setFieldError(passwordInput, mensaje);
        }
    });
}

const dosFactoresPanel = document.getElementById('dosFactoresPanel');
if (dosFactoresPanel) {
    initHabilitar2fa(dosFactoresPanel);
    initRegenerar2fa(dosFactoresPanel);
    initDeshabilitar2fa(dosFactoresPanel);
}
