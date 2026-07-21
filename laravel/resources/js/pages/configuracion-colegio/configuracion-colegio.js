import { toast } from '../../components/alerts/toast';
import { confirmAction } from '../../components/alerts/sweetAlert';
import { clearFormErrors, applyServerErrors } from '../../components/forms/validation';

// ---------- Logo: dropzone con preview, click/drag&drop y quitar ----------
const dropzone = document.getElementById('logoDropzone');

if (dropzone) {
    const input = document.getElementById('logoInput');
    const preview = document.getElementById('logoPreview');
    const placeholder = document.getElementById('logoPlaceholder');
    const removeBtn = document.getElementById('logoRemove');
    const removedFlag = document.getElementById('logoRemovido');

    const showPreview = (file) => {
        const reader = new FileReader();
        reader.onload = () => {
            preview.src = reader.result;
            preview.classList.remove('d-none');
            placeholder.classList.add('d-none');
            removeBtn.classList.remove('d-none');
            dropzone.classList.add('has-preview');
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

    dropzone.addEventListener('click', (event) => {
        if (event.target === removeBtn || removeBtn.contains(event.target)) return;
        input.click();
    });
    dropzone.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
        }
    });

    input.addEventListener('change', () => setFile(input.files[0]));

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('drag-over');
        });
    });
    ['dragleave', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('drag-over');
        });
    });
    dropzone.addEventListener('drop', (event) => setFile(event.dataTransfer.files[0]));

    removeBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        input.value = '';
        preview.src = '';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
        removeBtn.classList.add('d-none');
        dropzone.classList.remove('has-preview');
        removedFlag.value = '1';
    });
}

// ---------- Contador de caracteres de la descripción ----------
const descripcionInput = document.getElementById('descripcionInput');
const descripcionCount = document.getElementById('descripcionCount');

if (descripcionInput && descripcionCount) {
    descripcionInput.addEventListener('input', () => {
        descripcionCount.textContent = descripcionInput.value.length;
    });
}

// ---------- Formulario principal (identidad + ubicación/contacto) ----------
const formConfiguracion = document.getElementById('formConfiguracion');

if (formConfiguracion) {
    formConfiguracion.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(formConfiguracion);

        const submitBtns = document.querySelectorAll('#btnGuardarConfiguracion');
        submitBtns.forEach((btn) => {
            btn.disabled = true;
            btn.classList.add('btn-loading');
        });

        try {
            const formData = new FormData(formConfiguracion);
            const { data } = await window.axios.post(formConfiguracion.dataset.url, formData);

            toast.success(data.message);
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(formConfiguracion, response.data.errors);
                toast.error('Revisa los campos marcados.');
            } else {
                toast.error('No se pudo guardar. Intenta de nuevo.');
            }
        } finally {
            submitBtns.forEach((btn) => {
                btn.disabled = false;
                btn.classList.remove('btn-loading');
            });
        }
    });
}

// ---------- Formulario: nueva imagen de galería ----------
document.querySelectorAll('[data-galeria-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-loading');

        try {
            const formData = new FormData(form);
            const { data } = await window.axios.post(form.dataset.url, formData);

            toast.success(data.message);
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            const response = error.response;
            if (response?.status === 422 && response.data.errors) {
                applyServerErrors(form, response.data.errors);
                toast.error('Revisa los campos marcados.');
            } else if (response?.status === 422) {
                toast.error(response.data.message);
            } else {
                toast.error('No se pudo guardar. Intenta de nuevo.');
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
        }
    });
});

// ---------- Eliminar imagen de galería ----------
document.querySelectorAll('.g-del').forEach((btn) => {
    btn.addEventListener('click', async () => {
        const result = await confirmAction({
            title: '¿Eliminar esta imagen?',
            text: 'Se quitará de la galería institucional.',
            icon: 'warning',
            confirmText: 'Eliminar',
        });
        if (!result.isConfirmed) return;

        try {
            await window.axios.post(btn.dataset.url);
            toast.success('Imagen eliminada correctamente.');
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            toast.error('No se pudo eliminar. Intenta de nuevo.');
        }
    });
});

// ---------- Reordenar galería (drag & drop nativo) ----------
const galeriaGrid = document.getElementById('galeriaGrid');

if (galeriaGrid) {
    let draggedItem = null;

    galeriaGrid.querySelectorAll('.g-item[draggable="true"]').forEach((item) => {
        item.addEventListener('dragstart', () => {
            draggedItem = item;
            item.classList.add('dragging');
        });

        item.addEventListener('dragend', async () => {
            item.classList.remove('dragging');
            galeriaGrid.querySelectorAll('.g-item').forEach((el) => el.classList.remove('drag-over'));
            if (!draggedItem) return;
            draggedItem = null;

            const orden = Array.from(galeriaGrid.querySelectorAll('.g-item[data-id]')).map((el) => el.dataset.id);

            try {
                await window.axios.post(galeriaGrid.dataset.urlReordenar, { orden });
                toast.success('Orden actualizado correctamente.');
            } catch (error) {
                toast.error('No se pudo actualizar el orden.');
            }
        });

        item.addEventListener('dragover', (event) => {
            event.preventDefault();
            if (!draggedItem || draggedItem === item) return;
            item.classList.add('drag-over');

            const items = Array.from(galeriaGrid.querySelectorAll('.g-item[draggable="true"]'));
            const draggedIndex = items.indexOf(draggedItem);
            const targetIndex = items.indexOf(item);

            if (draggedIndex < targetIndex) {
                item.after(draggedItem);
            } else {
                item.before(draggedItem);
            }
        });

        item.addEventListener('dragleave', () => item.classList.remove('drag-over'));
    });
}
