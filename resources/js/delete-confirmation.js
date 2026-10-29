import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

export default function initializeDeleteConfirmation() {
    const pendingForms = new WeakSet();

    document.addEventListener('submit', event => {
        const form = event.target.closest('form[data-confirm-delete]');

        if (!form) {
            return;
        }

        event.preventDefault();

        if (pendingForms.has(form)) {
            return;
        }

        pendingForms.add(form);

        const isDarkMode = document.documentElement.dataset.adminMode === 'dark';

        Swal.fire({
            title: form.dataset.confirmTitle || 'Hapus data ini?',
            text: form.dataset.confirmText || 'Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            iconColor: '#e11d48',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            focusCancel: true,
            background: isDarkMode ? '#111827' : '#ffffff',
            color: isDarkMode ? '#f1f5f9' : '#0f172a',
        }).then(({ isConfirmed }) => {
            if (isConfirmed) {
                HTMLFormElement.prototype.submit.call(form);
            }
        }).finally(() => {
            pendingForms.delete(form);
        });
    });
}
