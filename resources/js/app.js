import './bootstrap';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Swal = Swal;

// ─── SweetAlert2 default theme ──────────────────────────────────────────────
window.toast = (message, type = 'success') => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });
};

// ─── Delete confirmation ─────────────────────────────────────────────────────
window.confirmDelete = (form, name = 'item ini') => {
    Swal.fire({
        title: 'Hapus ' + name + '?',
        text: 'Data yang dihapus tidak bisa dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
};

Alpine.start();
