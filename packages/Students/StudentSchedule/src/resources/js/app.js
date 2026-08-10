const closeLayer = (layer) => {
    layer?.classList.add('hidden');
    layer?.setAttribute('aria-hidden', 'true');
};

document.addEventListener('click', (event) => {
    const item = event.target.closest('[data-student-event]');
    if (item) {
        const data = JSON.parse(item.dataset.studentEvent || '{}');
        const drawer = document.getElementById('student-calendar-detail');
        drawer?.querySelector('[data-detail-title]')?.replaceChildren(data.title || '');
        drawer?.querySelector('[data-detail-kind]')?.replaceChildren(data.kind || '');
        drawer?.querySelector('[data-detail-time]')?.replaceChildren(data.time || '');
        drawer?.querySelector('[data-detail-classroom]')?.replaceChildren(data.classroom || '—');
        drawer?.querySelector('[data-detail-context]')?.replaceChildren(data.context || '');
        const action = drawer?.querySelector('[data-detail-action]');
        if (action) {
            action.href = data.url || '#';
            action.textContent = data.action || '';
            action.classList.toggle('hidden', !data.url || data.cancelled);
        }
        drawer?.classList.remove('hidden');
        drawer?.setAttribute('aria-hidden', 'false');
        return;
    }

    const closer = event.target.closest('[data-student-calendar-close]');
    if (closer) closeLayer(closer.closest('[data-student-calendar-layer]'));
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') document.querySelectorAll('[data-student-calendar-layer]:not(.hidden)').forEach(closeLayer);
});
