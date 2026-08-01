import { toast } from '../../components/alerts/toast';
import { confirmAction } from '../../components/alerts/sweetAlert';
import { setFieldError, clearFieldError } from '../../components/forms/validation';

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

function initHabilitar(panel) {
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

function initRegenerar(panel) {
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

function initDeshabilitar(panel) {
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

const panel = document.getElementById('dosFactoresPanel');
if (panel) {
    initHabilitar(panel);
    initRegenerar(panel);
    initDeshabilitar(panel);
}
