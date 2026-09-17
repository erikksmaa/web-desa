import 'bootstrap';

const confirmationModal = document.getElementById('confirmation-modal');

if (confirmationModal) {
    confirmationModal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const form = confirmationModal.querySelector('[data-confirm-form]');
        const item = confirmationModal.querySelector('[data-confirm-item]');
        const message = confirmationModal.querySelector('[data-confirm-message]');

        if (!(trigger instanceof HTMLElement) || !(form instanceof HTMLFormElement)) {
            event.preventDefault();

            return;
        }

        const action = trigger.dataset.confirmAction;

        if (!action) {
            event.preventDefault();

            return;
        }

        form.action = action;
        message.textContent = trigger.dataset.confirmMessage || 'Apakah Anda yakin ingin melanjutkan?';
        item.textContent = trigger.dataset.confirmItem || '';
        item.hidden = !trigger.dataset.confirmItem;
    });

    confirmationModal.addEventListener('hidden.bs.modal', () => {
        const form = confirmationModal.querySelector('[data-confirm-form]');
        const item = confirmationModal.querySelector('[data-confirm-item]');

        if (form instanceof HTMLFormElement) {
            form.removeAttribute('action');
        }

        if (item) {
            item.textContent = '';
            item.hidden = true;
        }
    });
}

document.querySelectorAll('[data-file-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        const fileName = document.querySelector(input.dataset.fileNameTarget);
        const preview = input.dataset.imagePreviewTarget
            ? document.querySelector(input.dataset.imagePreviewTarget)
            : null;

        if (fileName) {
            fileName.textContent = file ? `File dipilih: ${file.name}` : 'Belum ada file baru dipilih.';
        }

        if (!(preview instanceof HTMLImageElement)) {
            return;
        }

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
