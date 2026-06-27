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

        document.querySelectorAll('[data-exam-subject-picker]').forEach((picker) => {
            const field = picker.closest('.exam-field')?.querySelector('[data-exam-subject-value]');
            const button = picker.querySelector('[data-exam-subject-button]');
            const panel = picker.querySelector('[data-exam-subject-panel]');
            const label = picker.querySelector('[data-exam-subject-label]');
            const search = picker.querySelector('[data-exam-subject-search]');
            const list = picker.querySelector('[data-exam-subject-list]');
            const empty = picker.querySelector('.exam-subject-empty');
            const fallback = label?.textContent?.trim() || '';

            const filterList = (query) => {
                let visible = 0;

                list?.querySelectorAll('[data-value]').forEach((item) => {
                    const match = item.textContent.toLowerCase().includes(query.toLowerCase());
                    item.classList.toggle('hidden', !match);
                    if (match) {
                        visible += 1;
                    }
                });

                empty?.classList.toggle('hidden', visible > 0);
            };

            const selectSubject = (value, text) => {
                if (field) {
                    field.value = value;
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }

                if (label) {
                    label.textContent = text || fallback;
                    label.classList.toggle('text-slate-400', value === '');
                    label.classList.toggle('text-slate-800', value !== '');
                }

                list?.querySelectorAll('[data-value]').forEach((item) => {
                    item.classList.toggle('is-selected', item.dataset.value === value);
                });

                panel?.classList.add('hidden');
                if (search) {
                    search.value = '';
                    filterList('');
                }
            };

            button?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const isHidden = panel?.classList.contains('hidden');
                panel?.classList.toggle('hidden', !isHidden);
                if (isHidden) {
                    search?.focus();
                    filterList('');
                }
            });

            panel?.addEventListener('click', (event) => event.stopPropagation());
            search?.addEventListener('input', () => filterList(search.value));
            list?.addEventListener('click', (event) => {
                const item = event.target.closest('[data-value]');
                if (!item) {
                    return;
                }

                selectSubject(item.dataset.value || '', item.textContent.trim());
            });
            document.addEventListener('click', () => panel?.classList.add('hidden'));
        });

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

    document.querySelectorAll('[data-exam-topic-builder]').forEach((builder) => {
        const partLinks = [...builder.querySelectorAll('[data-exam-part-link]')];
        const partCards = [...builder.querySelectorAll('[data-exam-part]')];
        const indexButtons = [...builder.querySelectorAll('[data-exam-question-index-button]')];
        const countInputs = [...builder.querySelectorAll('[data-exam-count-input]')];
        const totalCount = builder.querySelector('[data-exam-total-count]');
        const progress = builder.querySelector('[data-exam-progress]');

        const setActivePart = (part) => {
            partLinks.forEach((link) => {
                link.classList.toggle('is-active', link.dataset.examPartLink === part);
            });

            partCards.forEach((card) => {
                card.classList.toggle('is-highlight', card.dataset.examPart === part);
            });
        };

        partLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const part = link.dataset.examPartLink;
                const target = builder.querySelector(`[data-exam-part="${part}"]`);

                if (!part || !target) {
                    return;
                }

                event.preventDefault();
                setActivePart(part);
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        indexButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.examQuestionIndexButton);
                if (!target) {
                    return;
                }

                indexButtons.forEach((item) => item.classList.toggle('is-active', item === button));
                setActivePart('source');
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });

        const syncCounts = () => {
            const total = countInputs.reduce((sum, input) => sum + Math.max(0, Number(input.value || 0)), 0);

            if (totalCount) {
                totalCount.textContent = String(total);
            }

            if (progress) {
                progress.style.width = `${Math.min(100, Math.max(12, total * 3))}%`;
            }

            countInputs.forEach((input) => {
                const button = builder.querySelector(`[data-exam-question-index-button="${input.dataset.indexTarget}"]`);
                button?.classList.toggle('is-filled', Number(input.value || 0) > 0);
            });
        };

        countInputs.forEach((input) => input.addEventListener('input', syncCounts));
        setActivePart('source');
        syncCounts();
    });

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
