import '../../../../Core/src/resources/js/mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
    const topicsSource = document.querySelector('[data-exam-subject-topics]');
    if (topicsSource) {
        const topicsBySubject = JSON.parse(topicsSource.textContent || '{}');

        const topicSelects = new Map(
            [...document.querySelectorAll('[data-exam-topic-select]')].map((select) => [select.dataset.topicName, select])
        );

        const fillTopics = (subjectSelect) => {
            const topicSelect = topicSelects.get(subjectSelect.dataset.topicTarget);

            if (!topicSelect) {
                return;
            }

            const current = topicSelect.dataset.currentValue || topicSelect.value || '';
            const topics = topicsBySubject[subjectSelect.value] || [];
            const placeholder = topicSelect.dataset.placeholder || '';
            topicSelect.innerHTML = '';
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholder;
            topicSelect.append(placeholderOption);

            topics.forEach((topic) => {
                const option = document.createElement('option');
                option.value = topic;
                option.textContent = topic;
                option.selected = topic === current;
                topicSelect.append(option);
            });

            if (current && !topics.includes(current)) {
                const option = document.createElement('option');
                option.value = current;
                option.textContent = current;
                option.selected = true;
                topicSelect.append(option);
            }

            topicSelect.disabled = !subjectSelect.value || (!topics.length && !current);
        };

        document.querySelectorAll('[data-exam-subject-select]').forEach((subjectSelect) => {
            fillTopics(subjectSelect);
            subjectSelect.addEventListener('change', () => {
                const topicSelect = topicSelects.get(subjectSelect.dataset.topicTarget);
                if (topicSelect) {
                    topicSelect.dataset.currentValue = '';
                }
                fillTopics(subjectSelect);
            });
        });
    }

    document.querySelectorAll('[data-exam-attempt]').forEach((page) => {
        const form = page.querySelector('[data-exam-form]');
        const timer = page.querySelector('[data-exam-timer]');
        const status = page.querySelector('[data-autosave-status]');
        const autosaveUrl = page.dataset.autosaveUrl;
        const violationUrl = page.dataset.violationUrl;
        const expiresAt = page.dataset.expiresAt ? new Date(page.dataset.expiresAt) : null;
        let autosaveTimer = null;
        let submitted = false;

        const setStatus = (text) => {
            if (status) {
                status.textContent = text;
            }
        };

        const csrfToken = () => form?.querySelector('input[name="_token"]')?.value
            || document.querySelector('meta[name="csrf-token"]')?.content
            || '';

        const autosave = () => {
            if (!form || !autosaveUrl || submitted) {
                return;
            }

            const payload = new FormData(form);

            fetch(autosaveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: payload,
            })
                .then((response) => response.ok ? response.json() : null)
                .then((data) => {
                    if (!data?.ok) {
                        return;
                    }

                    const savedAt = data.saved_at ? new Date(data.saved_at) : new Date();
                    setStatus(`${page.dataset.savedLabel || 'Saved'} ${savedAt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`);
                })
                .catch(() => setStatus(page.dataset.saveErrorLabel || 'Save failed'));
        };

        const scheduleAutosave = () => {
            window.clearTimeout(autosaveTimer);
            autosaveTimer = window.setTimeout(autosave, 600);
        };

        const updateTimer = () => {
            if (!timer || !expiresAt) {
                return;
            }

            const remaining = Math.max(0, expiresAt.getTime() - Date.now());
            const totalSeconds = Math.floor(remaining / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            timer.textContent = hours > 0
                ? `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
                : `${minutes}:${String(seconds).padStart(2, '0')}`;

            timer.classList.toggle('is-danger', totalSeconds <= 300);

            if (remaining <= 0 && form && !submitted) {
                submitted = true;
                form.submit();
            }
        };

        const logViolation = () => {
            if (!violationUrl || submitted) {
                return;
            }

            fetch(violationUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).catch(() => {});
        };

        form?.addEventListener('change', scheduleAutosave);
        form?.addEventListener('input', scheduleAutosave);
        form?.addEventListener('submit', () => {
            submitted = true;
            autosave();
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                logViolation();
                autosave();
            }
        });

        updateTimer();
        window.setInterval(updateTimer, 1000);
        window.setInterval(autosave, 15000);
    });
});
