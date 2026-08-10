const openLayer = (id) => {
    const layer = document.getElementById(id);
    layer?.classList.remove('hidden');
    layer?.setAttribute('aria-hidden', 'false');
};

const closeLayer = (layer) => {
    layer?.classList.add('hidden');
    layer?.setAttribute('aria-hidden', 'true');
};

let selectedEvent = null;

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
        selectedEvent = data;
        document.querySelector('[data-event-title]').textContent = data.title || '';
        document.querySelector('[data-event-kind]').textContent = data.kindLabel || '';
        document.querySelector('[data-event-time]').textContent = data.time || '';
        document.querySelector('[data-event-classroom]').textContent = data.classroom || '—';
        document.querySelector('[data-event-status]').textContent = data.statusLabel || '';
        document.querySelector('[data-event-reason]').textContent = data.reason || '';
        document.querySelector('[data-event-reason-shell]')?.classList.toggle('hidden', !data.reason);
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
        const completeForm = document.querySelector('[data-event-complete-form]');
        if (completeForm) {
            completeForm.action = data.completeUrl || '#';
            completeForm.classList.toggle('hidden', !data.completeUrl);
        }
        const edit = document.querySelector('[data-event-edit]');
        edit?.classList.toggle('hidden', !data.updateUrl);
        edit?.classList.toggle('flex', Boolean(data.updateUrl));
        const reschedule = document.querySelector('[data-event-reschedule]');
        reschedule?.classList.toggle('hidden', !data.rescheduleUrl);
        reschedule?.classList.toggle('flex', Boolean(data.rescheduleUrl));
        openLayer('calendar-detail-drawer');
        return;
    }

    if (event.target.closest('[data-event-edit]') && selectedEvent?.updateUrl) {
        const form = document.querySelector('[data-calendar-edit-form]');
        form.action = selectedEvent.updateUrl;
        ['title', 'location', 'meeting_url', 'description'].forEach((name) => {
            form.elements[name].value = selectedEvent[name === 'meeting_url' ? 'meetingUrl' : name] || '';
        });
        form.elements.delivery_mode.value = selectedEvent.deliveryMode || 'offline';
        closeLayer(document.getElementById('calendar-detail-drawer'));
        openLayer('calendar-edit-drawer');
        return;
    }

    if (event.target.closest('[data-event-reschedule]') && selectedEvent?.rescheduleUrl) {
        const form = document.querySelector('[data-calendar-reschedule-form]');
        form.action = selectedEvent.rescheduleUrl;
        const values = {
            type: selectedEvent.type || 'regular', lesson_id: selectedEvent.lessonId || '',
            delivery_mode: selectedEvent.deliveryMode || 'offline', title: selectedEvent.title || '',
            location: selectedEvent.location || '', meeting_url: selectedEvent.meetingUrl || '',
            description: selectedEvent.description || '', makeup_reason: selectedEvent.makeupReason || '',
            session_date: selectedEvent.date || '', start_time: selectedEvent.start || '', end_time: selectedEvent.end || '',
        };
        Object.entries(values).forEach(([name, value]) => { form.elements[name].value = value; });
        form.elements.reschedule_reason.value = '';
        closeLayer(document.getElementById('calendar-detail-drawer'));
        openLayer('calendar-reschedule-drawer');
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
