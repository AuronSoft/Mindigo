document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-support-auto-submit]').forEach((field) => {
        field.addEventListener('change', () => field.form?.requestSubmit());
    });
});
