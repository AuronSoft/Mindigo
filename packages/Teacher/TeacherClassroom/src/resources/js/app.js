const formatDateValue = (value) => {
    if (!value) {
        return '';
    }

    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return value;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return [
        date.getFullYear(),
        String(date.getMonth() + 1).padStart(2, '0'),
        String(date.getDate()).padStart(2, '0'),
    ].join('-');
};

const fillScheduleForm = (data = {}) => {
    const title = document.getElementById('schedule-modal-title');
    const method = document.getElementById('schedule-form-method');
    const form = document.getElementById('schedule-form');
    const titleInput = document.getElementById('schedule-title');
    const dateInput = document.getElementById('schedule-date');
    const startInput = document.getElementById('schedule-start');
    const endInput = document.getElementById('schedule-end');
    const descInput = document.getElementById('schedule-desc');

    if (!form || !method) {
        return;
    }

    title && (title.textContent = data.modalTitle || '');
    method.value = data.method || 'POST';
    form.action = data.action || form.dataset.storeUrl || form.action;
    titleInput && (titleInput.value = data.title || '');
    dateInput && (dateInput.value = formatDateValue(data.sessionDate) || form.dataset.defaultDate || '');
    startInput && (startInput.value = (data.startTime || form.dataset.defaultStart || '08:00').substring(0, 5));
    endInput && (endInput.value = (data.endTime || form.dataset.defaultEnd || '10:00').substring(0, 5));
    descInput && (descInput.value = data.description || '');
};

document.addEventListener('click', (event) => {
    const addTrigger = event.target.closest('[data-schedule-add]');
    if (addTrigger) {
        fillScheduleForm({
            modalTitle: addTrigger.dataset.modalTitle,
            method: 'POST',
            action: addTrigger.dataset.storeUrl,
            sessionDate: addTrigger.dataset.defaultDate,
            startTime: addTrigger.dataset.defaultStart,
            endTime: addTrigger.dataset.defaultEnd,
        });
        window.MindigoOpenModal?.('schedule-modal');
        return;
    }

    const editTrigger = event.target.closest('[data-schedule-edit]');
    if (editTrigger) {
        fillScheduleForm({
            modalTitle: editTrigger.dataset.modalTitle,
            method: 'PUT',
            action: editTrigger.dataset.updateUrl,
            title: editTrigger.dataset.title,
            sessionDate: editTrigger.dataset.sessionDate,
            startTime: editTrigger.dataset.startTime,
            endTime: editTrigger.dataset.endTime,
            description: editTrigger.dataset.description,
        });
        window.MindigoOpenModal?.('schedule-modal');
    }
});

const normalizeSubjectSearch = (value) => value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase();

document.querySelectorAll('[data-classroom-subject-picker]').forEach((picker) => {
    const trigger = picker.querySelector('[data-classroom-subject-trigger]');
    const panel = picker.querySelector('[data-classroom-subject-panel]');
    const search = picker.querySelector('[data-classroom-subject-search]');
    const select = picker.querySelector('[data-classroom-subject-select]');
    const label = picker.querySelector('[data-classroom-subject-label]');
    const options = [...picker.querySelectorAll('[data-classroom-subject-option]')];
    const empty = picker.querySelector('[data-classroom-subject-empty]');

    const close = () => {
        panel?.classList.add('hidden');
        trigger?.setAttribute('aria-expanded', 'false');
    };

    trigger?.addEventListener('click', () => {
        const opening = panel?.classList.contains('hidden');
        panel?.classList.toggle('hidden', !opening);
        trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
        if (opening) window.requestAnimationFrame(() => search?.focus());
    });

    search?.addEventListener('input', () => {
        const keyword = normalizeSubjectSearch(search.value.trim());
        let visible = 0;

        options.forEach((option) => {
            const matches = normalizeSubjectSearch(option.dataset.label || '').includes(keyword);
            option.classList.toggle('hidden', !matches);
            if (matches) visible += 1;
        });

        empty?.classList.toggle('hidden', visible > 0);
    });

    options.forEach((option) => option.addEventListener('click', () => {
        select.value = option.dataset.value || '';
        select.dispatchEvent(new Event('change', { bubbles: true }));
        label.textContent = option.dataset.label || '';
        label.classList.remove('text-slate-400');
        search.value = '';
        options.forEach((item) => item.classList.remove('hidden'));
        empty?.classList.add('hidden');
        close();
        trigger?.focus();
    }));

    picker.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });

    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) close();
    });
});
