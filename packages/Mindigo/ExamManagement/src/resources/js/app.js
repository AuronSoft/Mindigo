import '../../../../Core/src/resources/js/mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
    const compactDropdowns = [...document.querySelectorAll('[data-exam-compact-dropdown]')];
    compactDropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-exam-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-exam-dropdown-menu]');

        trigger?.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = menu?.classList.contains('hidden');
            compactDropdowns.forEach((item) => item.querySelector('[data-exam-dropdown-menu]')?.classList.add('hidden'));
            menu?.classList.toggle('hidden', !willOpen);
        });
        menu?.addEventListener('click', (event) => event.stopPropagation());
    });
    document.addEventListener('click', () => compactDropdowns.forEach((dropdown) => {
        dropdown.querySelector('[data-exam-dropdown-menu]')?.classList.add('hidden');
    }));
    document.querySelectorAll('[data-exam-source-mode]').forEach((option) => option.addEventListener('click', () => {
        option.closest('[data-exam-compact-dropdown]')?.querySelector('[data-exam-dropdown-menu]')?.classList.add('hidden');
    }));

    document.querySelectorAll('[data-exam-classrooms-toggle]').forEach((button) => {
        const list = button.parentElement?.nextElementSibling;
        const checkboxes = [...(list?.querySelectorAll('input[name="classroom_ids[]"]') || [])];

        const refresh = () => {
            const allSelected = checkboxes.length > 0 && checkboxes.every((checkbox) => checkbox.checked);
            button.textContent = allSelected ? button.dataset.clearLabel : button.dataset.selectLabel;
        };

        button.addEventListener('click', () => {
            const shouldSelect = !checkboxes.every((checkbox) => checkbox.checked);
            checkboxes.forEach((checkbox) => {
                checkbox.checked = shouldSelect;
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
            refresh();
        });
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refresh));
        refresh();
    });

    document.querySelectorAll('[data-exam-datetime-field]').forEach((field) => {
        const display = field.querySelector('[data-exam-datetime-display]');
        const picker = field.querySelector('[data-exam-datetime-picker]');
        const trigger = field.querySelector('[data-exam-datetime-trigger]');

        const openPicker = () => {
            if (typeof picker?.showPicker === 'function') {
                picker.showPicker();
                return;
            }

            picker?.click();
        };

        display?.addEventListener('click', openPicker);
        trigger?.addEventListener('click', openPicker);
        picker?.addEventListener('change', () => {
            if (!display || !picker.value) {
                if (display) display.value = '';
                return;
            }

            const [date, time = '00:00'] = picker.value.split('T');
            const [year, month, day] = date.split('-');
            display.value = `${day}/${month}/${year} ${time.slice(0, 5)}`;
            display.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

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
        const questionIndex = builder.querySelector('[data-exam-question-index]');
        const countInputs = [...builder.querySelectorAll('[data-exam-count-input]')];
        const pointInputs = [...builder.querySelectorAll('input[name^="points["]')];
        const blueprintButtons = [...builder.querySelectorAll('[data-exam-blueprint]')];
        const assessmentPurpose = builder.querySelector('[data-exam-assessment-purpose]');
        const durationInput = builder.querySelector('[name="duration_minutes"]');
        const passingScoreInput = builder.querySelector('[name="passing_score"]');
        const totalCount = builder.querySelector('[data-exam-total-count]');
        const progress = builder.querySelector('[data-exam-progress]');
        const wizardProgress = builder.querySelector('[data-exam-wizard-progress]');
        const previousButton = builder.querySelector('[data-exam-wizard-previous]');
        const nextButton = builder.querySelector('[data-exam-wizard-next]');
        const submitActions = builder.querySelector('.exam-studio-submit-actions');
        const steps = partLinks.map((link) => link.dataset.examPartLink);
        let activeStep = 0;

        const visibleFieldsAreValid = (card) => [...card.querySelectorAll('input, select, textarea')]
            .filter((field) => !field.disabled && field.type !== 'hidden')
            .every((field) => field.reportValidity());

        const validateStep = (index) => {
            const card = partCards.find((item) => item.dataset.examPart === steps[index]);
            if (!card) return true;

            if (steps[index] === 'source') {
                const count = countInputs.reduce((sum, input) => sum + Math.max(0, Number(input.value || 0)), 0);
                if (count === 0) {
                    window.alert(builder.dataset.countError || builder.dataset.stepError);
                    countInputs[0]?.focus();
                    return false;
                }
            }

            return visibleFieldsAreValid(card);
        };

        const syncReview = () => {
            const title = builder.closest('form')?.querySelector('[name="title"]')?.value?.trim() || '—';
            const duration = builder.querySelector('[name="duration_minutes"]')?.value || '—';
            const classrooms = builder.querySelectorAll('[name="classroom_ids[]"]:checked').length;
            const questions = countInputs.reduce((sum, input) => sum + Math.max(0, Number(input.value || 0)), 0);
            const values = { title, duration, classrooms, questions };
            Object.entries(values).forEach(([name, value]) => {
                const target = builder.querySelector(`[data-exam-review-${name}]`);
                if (target) target.textContent = String(value);
            });
        };

        const setActivePart = (part) => {
            partLinks.forEach((link) => {
                link.classList.toggle('is-active', link.dataset.examPartLink === part);
            });

            partCards.forEach((card) => {
                const active = card.dataset.examPart === part;
                card.classList.toggle('is-highlight', active);
                card.classList.toggle('is-active', active);
            });

            activeStep = Math.max(0, steps.indexOf(part));
            previousButton?.classList.toggle('hidden', activeStep === 0);
            nextButton?.classList.toggle('hidden', activeStep === steps.length - 1);
            submitActions?.classList.toggle('hidden', activeStep !== steps.length - 1);
            if (wizardProgress) wizardProgress.style.width = `${((activeStep + 1) / steps.length) * 100}%`;
            if (part === 'review') syncReview();
        };

        partLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const part = link.dataset.examPartLink;
                const target = builder.querySelector(`[data-exam-part="${part}"]`);

                if (!part || !target) {
                    return;
                }

                event.preventDefault();
                const targetIndex = steps.indexOf(part);
                if (targetIndex > activeStep && !validateStep(activeStep)) return;
                const allowedPart = targetIndex > activeStep + 1 ? steps[activeStep + 1] : part;
                setActivePart(allowedPart);
                builder.querySelector('.exam-studio-main')?.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        previousButton?.addEventListener('click', () => {
            if (activeStep > 0) setActivePart(steps[activeStep - 1]);
        });

        nextButton?.addEventListener('click', () => {
            if (activeStep < steps.length - 1 && validateStep(activeStep)) {
                setActivePart(steps[activeStep + 1]);
            }
        });

        const renderQuestionIndex = () => {
            if (!questionIndex) {
                return;
            }

            questionIndex.innerHTML = '';

            let questionNumber = 1;
            countInputs.forEach((input) => {
                const count = Math.max(0, Number(input.value || 0));
                const targetId = input.dataset.indexTarget;
                const target = targetId ? document.getElementById(targetId) : null;
                const title = target?.querySelector('strong')?.textContent?.trim() || '';

                for (let i = 0; i < count; i += 1) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = String(questionNumber);
                    button.dataset.examQuestionIndexButton = targetId || '';
                    button.title = title;
                    button.classList.toggle('is-active', questionNumber === 1);
                    questionIndex.append(button);
                    questionNumber += 1;
                }
            });
        };

        questionIndex?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-exam-question-index-button]');

            if (!button) {
                return;
            }

            const target = document.getElementById(button.dataset.examQuestionIndexButton);

            if (!target) {
                return;
            }

            questionIndex.querySelectorAll('[data-exam-question-index-button]').forEach((item) => {
                item.classList.toggle('is-active', item === button);
            });
            setActivePart('source');
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        const syncCounts = () => {
            const total = countInputs.reduce((sum, input) => sum + Math.max(0, Number(input.value || 0)), 0);
            const objectiveTypes = new Set(['single_choice', 'multiple_choice', 'true_false']);
            const objectiveTotal = countInputs.reduce((sum, input) => {
                const type = input.name.match(/counts\[([^\]]+)]/)?.[1];
                return sum + (objectiveTypes.has(type) ? Math.max(0, Number(input.value || 0)) : 0);
            }, 0);
            const totalPoints = countInputs.reduce((sum, input) => {
                const type = input.name.match(/counts\[([^\]]+)]/)?.[1];
                const points = pointInputs.find((pointInput) => pointInput.name === `points[${type}]`);
                return sum + (Math.max(0, Number(input.value || 0)) * Math.max(0, Number(points?.value || 0)));
            }, 0);

            if (totalCount) {
                totalCount.textContent = String(total);
            }

            if (progress) {
                progress.style.width = `${Math.min(100, Math.max(12, total * 3))}%`;
            }

            const structureTotal = builder.querySelector('[data-exam-structure-total]');
            const structurePoints = builder.querySelector('[data-exam-structure-points]');
            const objectiveRatio = builder.querySelector('[data-exam-objective-ratio]');
            if (structureTotal) structureTotal.textContent = String(total);
            if (structurePoints) structurePoints.textContent = String(Number(totalPoints.toFixed(2)));
            if (objectiveRatio) objectiveRatio.textContent = `${total > 0 ? Math.round((objectiveTotal / total) * 100) : 0}%`;

            renderQuestionIndex();
        };

        countInputs.forEach((input) => input.addEventListener('input', syncCounts));
        pointInputs.forEach((input) => input.addEventListener('input', syncCounts));
        blueprintButtons.forEach((button) => button.addEventListener('click', () => {
            const counts = JSON.parse(button.dataset.counts || '{}');
            countInputs.forEach((input) => {
                const type = input.name.match(/counts\[([^\]]+)]/)?.[1];
                input.value = String(counts[type] ?? 0);
            });
            if (durationInput) durationInput.value = button.dataset.duration || durationInput.value;
            if (passingScoreInput) passingScoreInput.value = button.dataset.passing || passingScoreInput.value;
            if (assessmentPurpose) assessmentPurpose.value = button.dataset.purpose || 'formative';
            blueprintButtons.forEach((item) => item.classList.toggle('is-active', item === button));
            const dropdown = button.closest('[data-exam-compact-dropdown]');
            const blueprintLabel = dropdown?.querySelector('[data-exam-blueprint-label]');
            const blueprintMeta = dropdown?.querySelector('[data-exam-blueprint-meta]');
            if (blueprintLabel) blueprintLabel.textContent = button.dataset.label || '';
            if (blueprintMeta) blueprintMeta.textContent = button.dataset.meta || '';
            dropdown?.querySelector('[data-exam-dropdown-menu]')?.classList.add('hidden');
            syncCounts();
        }));
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
