const openLayer = (id) => {
    const layer = document.getElementById(id);
    layer?.classList.remove('hidden');
    layer?.setAttribute('aria-hidden', 'false');
};

const closeLayer = (layer) => {
    layer?.classList.add('hidden');
    layer?.setAttribute('aria-hidden', 'true');
};

document.addEventListener('click', (event) => {
    const create = event.target.closest('[data-calendar-create]');
    if (create) {
        const form = document.getElementById('calendar-session-form');
        if (form) {
            form.reset();
            form.querySelector('[name="session_date"]').value = create.dataset.date || '';
            form.querySelector('[name="start_time"]').value = create.dataset.start || '08:00';
            form.querySelector('[name="end_time"]').value = create.dataset.end || '10:00';
        }
        openLayer('calendar-create-drawer');
        return;
    }

    const item = event.target.closest('[data-calendar-event]');
    if (item) {
        const data = JSON.parse(item.dataset.calendarEvent || '{}');
        document.querySelector('[data-event-title]').textContent = data.title || '';
        document.querySelector('[data-event-kind]').textContent = data.kindLabel || '';
        document.querySelector('[data-event-time]').textContent = data.time || '';
        document.querySelector('[data-event-classroom]').textContent = data.classroom || '—';
        const link = document.querySelector('[data-event-link]');
        if (link) {
            link.href = data.url || '#';
            link.classList.toggle('hidden', !data.url);
        }
        const cancelForm = document.querySelector('[data-event-cancel-form]');
        if (cancelForm) {
            cancelForm.action = data.cancelUrl || '#';
            cancelForm.classList.toggle('hidden', !data.cancelUrl);
        }
        openLayer('calendar-detail-drawer');
        return;
    }

    const closer = event.target.closest('[data-calendar-close]');
    if (closer) closeLayer(closer.closest('[data-calendar-layer]'));
});

document.getElementById('calendar-classroom')?.addEventListener('change', (event) => {
    const option = event.target.selectedOptions[0];
    const form = event.target.form;
    if (form && option?.dataset.storeUrl) form.action = option.dataset.storeUrl;
    const typeShell = document.getElementById('calendar-session-type-shell');
    typeShell?.classList.toggle('hidden', option?.dataset.type !== 'course');
    const lessonSelect = document.getElementById('calendar-lesson');
    lessonSelect?.querySelectorAll('option[data-classroom]').forEach((lesson) => {
        lesson.hidden = lesson.dataset.classroom !== event.target.value;
    });
    if (lessonSelect) lessonSelect.value = '';
});

document.getElementById('calendar-session-type')?.addEventListener('change', (event) => {
    const field = document.getElementById('calendar-makeup-reason-shell');
    const input = field?.querySelector('textarea');
    const active = event.target.value === 'makeup';
    field?.classList.toggle('hidden', !active);
    if (input) input.required = active;
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-calendar-layer]:not(.hidden)').forEach(closeLayer);
});
