import '../../../../Core/src/resources/js/mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-user-auto-submit]').forEach((field) => {
        field.addEventListener('change', () => field.form?.requestSubmit());
    });
});
