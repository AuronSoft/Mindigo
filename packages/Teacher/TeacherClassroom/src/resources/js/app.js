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
