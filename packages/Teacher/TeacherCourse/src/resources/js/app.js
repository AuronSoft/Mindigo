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

const normalizeMasterSearch = (value) => value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase();

document.querySelectorAll('[data-course-master-picker]').forEach((picker) => {
    const trigger = picker.querySelector('[data-course-master-trigger]');
    const panel = picker.querySelector('[data-course-master-panel]');
    const search = picker.querySelector('[data-course-master-search]');
    const select = picker.querySelector('[data-course-master-select]');
    const label = picker.querySelector('[data-course-master-label]');
    const options = Array.from(picker.querySelectorAll('[data-course-master-option]'));
    const empty = picker.querySelector('[data-course-master-empty]');

    const close = () => {
        panel?.classList.add('hidden');
        trigger?.setAttribute('aria-expanded', 'false');
    };

    trigger?.addEventListener('click', () => {
        const opening = panel?.classList.contains('hidden');
        document.querySelectorAll('[data-course-master-panel]').forEach((item) => item.classList.add('hidden'));
        panel?.classList.toggle('hidden', !opening);
        trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
        if (opening) window.requestAnimationFrame(() => search?.focus());
    });

    search?.addEventListener('input', () => {
        const keyword = normalizeMasterSearch(search.value.trim());
        let visible = 0;
        options.forEach((option) => {
            const matches = option.dataset.value === '' || normalizeMasterSearch(option.dataset.label || '').includes(keyword);
            option.classList.toggle('hidden', !matches);
            if (matches && option.dataset.value !== '') visible += 1;
        });
        empty?.classList.toggle('hidden', visible > 0);
    });

    options.forEach((option) => option.addEventListener('click', () => {
        if (select) {
            select.value = option.dataset.value || '';
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (label) {
            label.textContent = option.dataset.label || '';
            label.classList.toggle('text-slate-400', !option.dataset.value);
        }
        if (search) search.value = '';
        options.forEach((item) => item.classList.remove('hidden'));
        empty?.classList.add('hidden');
        close();
        trigger?.focus();
    }));

    picker.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
            trigger?.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) close();
    });
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
