document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-course-chapter-edit]');

    if (!trigger) {
        return;
    }

    const form = document.getElementById('edit-chapter-form');
    const input = document.getElementById('edit-chapter-name');
    const template = trigger.dataset.updateUrl;

    if (!form || !input || !template) {
        return;
    }

    form.action = template.replace(':chapter', trigger.dataset.chapterId);
    input.value = trigger.dataset.chapterName || '';
    window.MindigoOpenModal?.('edit-chapter-modal');
});
