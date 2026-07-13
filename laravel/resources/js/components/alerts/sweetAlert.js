import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

export function confirmAction({
    title = '¿Estás seguro?',
    text = '',
    icon = 'warning',
    confirmText = 'Sí, continuar',
    cancelText = 'Cancelar',
} = {}) {
    return Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true,
        focusCancel: true,
        buttonsStyling: false,
        customClass: {
            popup: 'cs-swal',
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-secondary',
        },
    });
}

export function confirmLogout() {
    return confirmAction({
        title: '¿Cerrar sesión?',
        text: 'Tendrás que volver a iniciar sesión para acceder al panel.',
        icon: 'question',
        confirmText: 'Cerrar sesión',
    });
}
