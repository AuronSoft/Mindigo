import '../../../../Core/src/resources/js/Mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
    if (window.__systemSettingSuccess) {
        MindigoToast(window.__systemSettingSuccess, 'success');
    }

    if (window.__systemSettingInfo) {
        MindigoToast(window.__systemSettingInfo, 'info');
    }

    if (Array.isArray(window.__systemSettingErrors)) {
        window.__systemSettingErrors.forEach((message, index) => {
            if (!message) return;
            setTimeout(() => MindigoToast(message, 'error', 4200), index * 180);
        });
    }

    const form = document.getElementById('system-setting-form');
    if (!form) return;

    const initialSnapshot = snapshotForm(form);
    let confirmedSubmit = false;

    form.addEventListener('submit', async (event) => {
        if (confirmedSubmit) {
            return;
        }

        event.preventDefault();

        const currentSnapshot = snapshotForm(form);

        if (currentSnapshot === initialSnapshot) {
            MindigoToast('Chưa có trường dữ liệu nào thay đổi.', 'info');
            return;
        }

        const confirmed = await MindigoConfirm({
            title: 'Lưu cấu hình hệ thống',
            message: 'Các thay đổi sẽ được áp dụng cho toàn bộ hệ thống. Bạn có chắc chắn muốn lưu không?',
            confirmText: 'Lưu cấu hình',
            cancelText: 'Huỷ',
            type: 'warning',
        });

        if (!confirmed) {
            return;
        }

        confirmedSubmit = true;
        form.submit();
    });
});

function snapshotForm(form) {
    const fields = Array.from(form.querySelectorAll('[data-system-setting-field]'));
    const values = fields.map((field) => {
        const name = field.getAttribute('name');
        const value = field.type === 'checkbox'
            ? (field.checked ? '1' : '0')
            : String(field.value ?? '').trim();

        return [name, value];
    });

    values.sort(([left], [right]) => left.localeCompare(right));

    return JSON.stringify(values);
}
