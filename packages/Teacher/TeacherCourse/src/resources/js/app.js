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

const curriculum = document.querySelector('[data-course-curriculum]');

if (curriculum) {
    let dragged = null;

    curriculum.addEventListener('dragstart', (event) => {
        dragged = event.target.closest('[data-lesson-id], [data-chapter-id]');
        dragged?.classList.add('opacity-50');
    });
    curriculum.addEventListener('dragend', () => {
        dragged?.classList.remove('opacity-50');
        dragged = null;
    });
    curriculum.addEventListener('dragover', (event) => {
        const selector = dragged?.matches('[data-lesson-id]') ? '[data-lesson-id]' : '[data-chapter-id]';
        const target = event.target.closest(selector);
        if (!dragged || !target || target === dragged || (dragged.matches('[data-lesson-id]') && dragged.parentElement !== target.parentElement)) return;
        event.preventDefault();
        target.parentElement.insertBefore(dragged, target);
    });
    curriculum.addEventListener('drop', async (event) => {
        event.preventDefault();
        const chapters = [...curriculum.querySelectorAll(':scope > [data-chapter-id]')].map((chapter, order) => ({
            id: Number(chapter.dataset.chapterId), order,
            lessons: [...chapter.querySelectorAll('[data-lesson-id]')].map((lesson, lessonOrder) => ({ id: Number(lesson.dataset.lessonId), order: lessonOrder })),
        }));
        try {
            const response = await fetch(curriculum.dataset.reorderUrl, { method: 'PATCH', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: JSON.stringify({ chapters }) });
            if (!response.ok) throw new Error('order');
        } catch {
            window.alert(curriculum.dataset.orderError);
        }
    });
}
