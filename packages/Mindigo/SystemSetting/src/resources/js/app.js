import '../../../../Core/src/resources/js/mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
    const messages = window.__systemSettingMessages || {};

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
            MindigoToast(messages.no_changes || 'No settings were changed.', 'info');
            return;
        }

        const confirmed = await MindigoConfirm({
            title: messages.confirm_title || 'Save system settings',
            message: messages.confirm_message || 'These changes will apply across the system. Are you sure you want to save?',
            confirmText: messages.confirm_text || 'Save settings',
            cancelText: messages.cancel_text || 'Cancel',
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
