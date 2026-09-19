import { Offcanvas } from 'bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const swalClasses = {
    popup: 'admin-swal-popup',
    confirmButton: 'btn btn-primary px-4',
    cancelButton: 'btn btn-outline-secondary px-4',
    actions: 'gap-2',
};

const fireAlert = (options) => Swal.fire({
    buttonsStyling: false,
    customClass: swalClasses,
    ...options,
});

const initializeSidebar = () => {
    const root = document.documentElement;
    const sidebar = document.getElementById('admin-sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (!(sidebar instanceof HTMLElement)) return;

    const syncToggle = () => {
        const collapsed = root.classList.contains('admin-sidebar-collapsed');
        if (toggle instanceof HTMLButtonElement) {
            toggle.setAttribute('aria-expanded', String(!collapsed));
            toggle.setAttribute('aria-label', collapsed ? 'Perluas menu admin' : 'Ciutkan menu admin');
        }
    };

    syncToggle();

    toggle?.addEventListener('click', () => {
        const collapsed = root.classList.toggle('admin-sidebar-collapsed');
        try {
            localStorage.setItem('adminSidebarCollapsed', String(collapsed));
        } catch (error) {}
        syncToggle();
    });

    sidebar.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 991.98px)').matches) {
                Offcanvas.getOrCreateInstance(sidebar).hide();
            }
        });
    });
};

const initializeConfirmations = () => {
    const deleteForm = document.querySelector('[data-admin-delete-form]');

    document.addEventListener('click', async (event) => {
        const trigger = event.target instanceof Element ? event.target.closest('[data-delete-action]') : null;
        if (!(trigger instanceof HTMLButtonElement) || !(deleteForm instanceof HTMLFormElement)) return;

        event.preventDefault();
        const action = trigger.dataset.deleteAction;
        if (!action) return;

        const item = trigger.dataset.deleteItem || 'data ini';
        const result = await fireAlert({
            icon: 'warning',
            title: 'Hapus data?',
            text: trigger.dataset.deleteMessage || 'Data yang dihapus tidak dapat dipulihkan.',
            footer: item,
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#b4232f',
            reverseButtons: true,
            focusCancel: true,
            allowOutsideClick: false,
            customClass: {
                ...swalClasses,
                confirmButton: 'btn btn-danger px-4',
            },
        });

        if (!result.isConfirmed) return;

        const target = new URL(action, window.location.origin);
        if (target.origin !== window.location.origin) {
            await fireAlert({ icon: 'error', title: 'Tindakan dibatalkan', text: 'Alamat tujuan penghapusan tidak valid.' });
            return;
        }

        deleteForm.action = target.href;
        HTMLFormElement.prototype.submit.call(deleteForm);
    });

    document.querySelectorAll('[data-logout-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const result = await fireAlert({
                icon: 'question',
                title: 'Keluar dari panel admin?',
                text: 'Sesi pengelolaan konten akan diakhiri.',
                showCancelButton: true,
                confirmButtonText: 'Ya, keluar',
                cancelButtonText: 'Tetap di sini',
                reverseButtons: true,
                focusCancel: true,
                allowOutsideClick: false,
            });
            if (result.isConfirmed) HTMLFormElement.prototype.submit.call(form);
        });
    });
};

const showFeedback = async () => {
    const notifications = Array.isArray(window.adminFeedback) ? window.adminFeedback : [];
    for (const notification of notifications) {
        const isPassive = ['success', 'info'].includes(notification.icon);
        await fireAlert({
            icon: notification.icon,
            title: notification.title,
            text: notification.text,
            toast: isPassive,
            position: isPassive ? 'top-end' : 'center',
            timer: isPassive ? 4200 : undefined,
            timerProgressBar: isPassive,
            showConfirmButton: !isPassive,
            confirmButtonText: 'Mengerti',
        });
    }
};

const initializeFileInputs = () => {
    document.querySelectorAll('[data-file-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            const fileName = document.querySelector(input.dataset.fileNameTarget);
            const preview = input.dataset.imagePreviewTarget ? document.querySelector(input.dataset.imagePreviewTarget) : null;

            if (fileName) {
                fileName.textContent = input.files.length > 1
                    ? `${input.files.length} file dipilih: ${Array.from(input.files, (selected) => selected.name).join(', ')}`
                    : (file ? `File dipilih: ${file.name}` : 'Belum ada file baru dipilih.');
            }

            if (!(preview instanceof HTMLImageElement)) return;
            if (preview.dataset.objectUrl) {
                URL.revokeObjectURL(preview.dataset.objectUrl);
                delete preview.dataset.objectUrl;
            }
            if (!file || !file.type.startsWith('image/')) {
                preview.removeAttribute('src');
                preview.hidden = true;
                return;
            }
            const objectUrl = URL.createObjectURL(file);
            preview.dataset.objectUrl = objectUrl;
            preview.src = objectUrl;
            preview.hidden = false;
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initializeSidebar();
    initializeConfirmations();
    initializeFileInputs();
    showFeedback();
});
