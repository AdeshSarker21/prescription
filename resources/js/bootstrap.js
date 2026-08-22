import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Swal from 'sweetalert2';
window.Swal = Swal;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('submit', e => {
            e.preventDefault();
            const form = el;
            const message = el.getAttribute('data-confirm') || 'Are you sure?';
            const confirmText = el.getAttribute('data-confirm-text') || 'Yes, delete it!';
            const cancelText = el.getAttribute('data-cancel-text') || 'Cancel';
            const title = el.getAttribute('data-title') || 'Confirm';
            const icon = el.getAttribute('data-icon') || 'warning';

            Swal.fire({
                title,
                text: message,
                icon,
                showCancelButton: true,
                confirmButtonColor: el.getAttribute('data-confirm-color') || '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    const flash = document.querySelector('[data-flash-success]');
    if (flash) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: flash.dataset.flashSuccess,
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
    }

    const flashError = document.querySelector('[data-flash-error]');
    if (flashError) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: flashError.dataset.flashError,
            timer: 5000,
            showConfirmButton: true,
            toast: true,
            position: 'top-end',
        });
    }
});
