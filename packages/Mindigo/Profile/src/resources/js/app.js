import '../../../../Core/src/resources/js/Mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash?.replace('#', '');

    if (window.__profileSuccess) {
        MindigoToast(window.__profileSuccess, 'success');
    }

    if (Array.isArray(window.__profileErrors)) {
        window.__profileErrors.forEach((message, index) => {
            if (!message) return;
            setTimeout(() => MindigoToast(message, 'error', 4200), index * 180);
        });
    }

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

        if (saveBtn) {
            saveBtn.classList.toggle('hidden', target !== 'ho-so');
        }
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

    if (avatarInput && avPreview) {
        avatarInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                MindigoToast('Vui lòng chọn file ảnh hợp lệ.', 'error');
                avatarInput.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                MindigoToast('Ảnh không được vượt quá 2MB.', 'warning');
                avatarInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (readerEvent) => {
                avPreview.innerHTML = `<img src="${readerEvent.target.result}" alt="avatar" class="h-full w-full object-cover">`;
            };
            reader.readAsDataURL(file);
        });
    }

    const btnDelete = document.getElementById('btn-delete-account');
    if (btnDelete) {
        btnDelete.addEventListener('click', async () => {
            const confirmed = await MindigoConfirm({
                title: 'Xoá tài khoản?',
                message: 'Hành động này không thể hoàn tác. Toàn bộ dữ liệu của tài khoản sẽ bị xoá vĩnh viễn.',
                confirmText: 'Xoá ngay',
                cancelText: 'Huỷ',
                type: 'danger',
            });

            if (!confirmed) return;

            MindigoToast('Đang xử lý yêu cầu xoá tài khoản...', 'warning', 1200);
            submitProfileAction('/profile', 'DELETE');
        });
    }

    const btnSuspend = document.getElementById('btn-suspend-account');
    if (btnSuspend) {
        btnSuspend.addEventListener('click', async () => {
            const confirmed = await MindigoConfirm({
                title: 'Đình chỉ tài khoản?',
                message: 'Tài khoản sẽ bị tạm khoá và bạn sẽ bị đăng xuất ngay lập tức.',
                confirmText: 'Đình chỉ',
                cancelText: 'Huỷ',
                type: 'warning',
            });

            if (!confirmed) return;

            MindigoToast('Đang xử lý...', 'warning', 1200);
            submitProfileAction('/profile/suspend', 'POST');
        });
    }

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
