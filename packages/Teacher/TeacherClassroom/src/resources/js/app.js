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

const formatDateDisplay = (value) => {
    const isoDate = formatDateValue(value);

    if (!isoDate) {
        return '';
    }

    const [year, month, day] = isoDate.split('-');

    return `${day}/${month}/${year}`;
};

const openNativeDatePicker = (picker) => {
    if (!picker) {
        return;
    }

    if (typeof picker.showPicker === 'function') {
        picker.showPicker();
        return;
    }

    picker.click();
};

const fillScheduleForm = (data = {}) => {
    const title = document.getElementById('schedule-modal-title');
    const method = document.getElementById('schedule-form-method');
    const form = document.getElementById('schedule-form');
    const titleInput = document.getElementById('schedule-title');
    const dateInput = document.getElementById('schedule-date');
    const dateDisplay = document.getElementById('schedule-date-display');
    const datePicker = document.getElementById('schedule-date-picker');
    const startInput = document.getElementById('schedule-start');
    const endInput = document.getElementById('schedule-end');
    const descInput = document.getElementById('schedule-desc');
    const typeInput = document.getElementById('schedule-type');
    const makeupReasonInput = document.getElementById('schedule-makeup-reason');
    const substituteTeacherInput = document.getElementById('schedule-substitute-teacher');

    if (!form || !method) {
        return;
    }

    title && (title.textContent = data.modalTitle || '');
    method.value = data.method || 'POST';
    form.action = data.action || form.dataset.storeUrl || form.action;
    titleInput && (titleInput.value = data.title || '');
    const dateValue = formatDateValue(data.sessionDate) || form.dataset.defaultDate || '';
    dateInput && (dateInput.value = dateValue);
    dateDisplay && (dateDisplay.value = formatDateDisplay(dateValue));
    datePicker && (datePicker.value = dateValue);
    startInput && (startInput.value = (data.startTime || form.dataset.defaultStart || '08:00').substring(0, 5));
    endInput && (endInput.value = (data.endTime || form.dataset.defaultEnd || '10:00').substring(0, 5));
    descInput && (descInput.value = data.description || '');
    typeInput && (typeInput.value = data.type || 'regular');
    makeupReasonInput && (makeupReasonInput.value = data.makeupReason || '');
    substituteTeacherInput && (substituteTeacherInput.value = data.substituteTeacherId || '');
    typeInput?.dispatchEvent(new Event('change', { bubbles: true }));
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
            type: editTrigger.dataset.type,
            makeupReason: editTrigger.dataset.makeupReason,
            substituteTeacherId: editTrigger.dataset.substituteTeacherId,
        });
        window.MindigoOpenModal?.('schedule-modal');
    }
});

const scheduleType = document.getElementById('schedule-type');
const scheduleMakeupReasonField = document.getElementById('schedule-makeup-reason-field');
const scheduleMakeupReason = document.getElementById('schedule-makeup-reason');

scheduleType?.addEventListener('change', () => {
    const isMakeup = scheduleType.value === 'makeup';
    scheduleMakeupReasonField?.classList.toggle('hidden', !isMakeup);
    if (scheduleMakeupReason) {
        scheduleMakeupReason.required = isMakeup;
        if (!isMakeup) scheduleMakeupReason.value = '';
    }
});

const scheduleDateValue = document.getElementById('schedule-date');
const scheduleDateDisplay = document.getElementById('schedule-date-display');
const scheduleDatePicker = document.getElementById('schedule-date-picker');
const scheduleDateTrigger = document.getElementById('schedule-date-trigger');

[scheduleDateDisplay, scheduleDateTrigger].forEach((control) => {
    control?.addEventListener('click', () => openNativeDatePicker(scheduleDatePicker));
});

scheduleDatePicker?.addEventListener('change', () => {
    const value = formatDateValue(scheduleDatePicker.value);
    if (scheduleDateValue) scheduleDateValue.value = value;
    if (scheduleDateDisplay) scheduleDateDisplay.value = formatDateDisplay(value);
});

document.querySelectorAll('[data-attendance-date-form]').forEach((form) => {
    const valueInput = form.querySelector('[data-attendance-date-value]');
    const displayInput = form.querySelector('[data-attendance-date-display]');
    const picker = form.querySelector('[data-attendance-date-picker]');
    const trigger = form.querySelector('[data-attendance-date-trigger]');

    [displayInput, trigger].forEach((control) => {
        control?.addEventListener('click', () => openNativeDatePicker(picker));
    });

    picker?.addEventListener('change', () => {
        const value = formatDateValue(picker.value);
        if (!value) return;

        valueInput.value = value;
        displayInput.value = formatDateDisplay(value);
        form.requestSubmit();
    });
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
