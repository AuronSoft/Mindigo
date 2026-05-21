import '../../../../Core/src/resources/js/mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash?.replace('#', '');
    const messages = window.__profileMessages || {};
    const tabs = document.querySelectorAll('.profile-tab');
    const panels = document.querySelectorAll('.profile-tab-panel');
    const saveBtn = document.querySelector('.btn-profile-save[form="profile-form"]');

    const setActiveTab = (target) => {
        tabs.forEach(tab => {
            const active = tab.dataset.tab === target;
            tab.classList.toggle('text-green-700', active);
            tab.classList.toggle('border-green-500', active);
            tab.classList.toggle('text-slate-400', !active);
            tab.classList.toggle('border-transparent', !active);
        });

        panels.forEach(panel => {
            panel.classList.toggle('hidden', panel.id !== `panel-${target}`);
        });

        saveBtn?.classList.toggle('hidden', target !== 'ho-so');
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            setActiveTab(tab.dataset.tab);
            history.replaceState(null, '', `#${tab.dataset.tab}`);
        });
    });

    if (hash && document.querySelector(`.profile-tab[data-tab="${hash}"]`)) {
        setActiveTab(hash);
    }

    const avatarInput = document.getElementById('avatar-input');
    const avPreview = document.getElementById('av-preview');

    avatarInput?.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            MindigoToast(messages.invalid_image || 'Please select a valid image file.', 'error');
            avatarInput.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            MindigoToast(messages.image_too_large || 'Image must not exceed 2MB.', 'warning');
            avatarInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (readerEvent) => {
            avPreview.innerHTML = `<img src="${readerEvent.target.result}" alt="avatar" class="h-full w-full object-cover">`;
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('btn-delete-account')?.addEventListener('click', async () => {
        const confirmed = await MindigoConfirm({
            title: messages.delete_title || 'Delete account?',
            message: messages.delete_message || 'This action cannot be undone. Account data will be permanently deleted.',
            confirmText: messages.delete_confirm || 'Delete now',
            cancelText: messages.cancel || 'Cancel',
            type: 'danger',
        });

        if (!confirmed) return;

        MindigoToast(messages.deleting || 'Processing account deletion...', 'warning', 1200);
        submitProfileAction('/profile', 'DELETE');
    });

    document.getElementById('btn-suspend-account')?.addEventListener('click', async () => {
        const confirmed = await MindigoConfirm({
            title: messages.suspend_title || 'Suspend account?',
            message: messages.suspend_message || 'The account will be suspended and signed out immediately.',
            confirmText: messages.suspend_confirm || 'Suspend',
            cancelText: messages.cancel || 'Cancel',
            type: 'warning',
        });

        if (!confirmed) return;

        MindigoToast(messages.processing || 'Processing...', 'warning', 1200);
        submitProfileAction('/profile/suspend', 'POST');
    });
});

function submitProfileAction(action, method) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    form.appendChild(csrf);

    if (method !== 'POST') {
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = method;
        form.appendChild(methodInput);
    }

    document.body.appendChild(form);
    setTimeout(() => form.submit(), 400);
}
