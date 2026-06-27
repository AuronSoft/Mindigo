import '../../../../Core/src/resources/js/mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
    const topicsSource = document.querySelector('[data-question-subject-topics]');
    if (topicsSource) {
        const topicsBySubject = JSON.parse(topicsSource.textContent || '{}');
        const topicSelects = new Map(
            [...document.querySelectorAll('[data-question-topic-select]')].map((select) => [select.dataset.topicName, select])
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

        document.querySelectorAll('[data-question-subject-select]').forEach((subjectSelect) => {
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

    document.querySelectorAll('[data-question-auto-submit]').forEach((field) => {
        field.addEventListener('change', () => field.form?.requestSubmit());
    });

    document.querySelectorAll('[data-folder-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelector('[data-folder-form]')?.classList.toggle('hidden');
        });
    });

    document.querySelectorAll('[data-import-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelector('[data-import-form]')?.classList.toggle('hidden');
        });
    });

    document.querySelectorAll('[data-import-drop]').forEach((dropZone) => {
        const fileInput = dropZone.querySelector('[data-import-file]');
        const fileName = dropZone.querySelector('[data-import-file-name]');

        const setFileName = () => {
            if (fileInput?.files?.[0] && fileName) {
                fileName.textContent = fileInput.files[0].name;
            }
        };

        fileInput?.addEventListener('change', setFileName);

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropZone.classList.add('is-dragging');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropZone.classList.remove('is-dragging');
            });
        });

        dropZone.addEventListener('drop', (event) => {
            if (!fileInput || !event.dataTransfer?.files?.length) {
                return;
            }

            fileInput.files = event.dataTransfer.files;
            setFileName();
        });
    });

    document.querySelectorAll('[data-question-builder]').forEach((builder) => {
        const partLinks = [...builder.querySelectorAll('[data-question-part-link]')];
        const partCards = [...builder.querySelectorAll('[data-question-part]')];
        const questionIndex = builder.querySelector('[data-question-index]');
        const questionMain = builder.querySelector('.question-studio-main');
        const typeField = builder.querySelector('[data-question-type]');
        const optionPanel = builder.querySelector('[data-option-panel]');
        const shortAnswerPanel = builder.querySelector('[data-short-answer-panel]');
        const essayPanel = builder.querySelector('[data-essay-panel]');
        const optionList = builder.querySelector('[data-option-list]');
        const template = builder.querySelector('[data-option-template]');
        const addButton = builder.querySelector('[data-add-option]');

        const visiblePartCard = (part) => partCards.find((card) => card.dataset.questionPart === part && !card.classList.contains('hidden'));

        const setActiveQuestionIndex = (activeTarget) => {
            questionIndex?.querySelectorAll('[data-question-index-button]').forEach((button) => {
                button.classList.toggle('is-active', button.dataset.questionIndexButton === activeTarget.id);
            });
        };

        const renderQuestionIndex = () => {
            if (!questionIndex) {
                return;
            }

            const targets = [...builder.querySelectorAll('[data-question-index-target]')];
            questionIndex.innerHTML = '';

            targets.forEach((target, index) => {
                if (!target.id) {
                    target.id = `question-item-${index + 1}`;
                }

                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = String(index + 1);
                button.dataset.questionIndexButton = target.id;
                button.classList.toggle('is-active', index === 0);
                button.addEventListener('click', () => {
                    setActiveQuestionIndex(target);
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
                questionIndex.append(button);
            });
        };

        const setActivePart = (part) => {
            partLinks.forEach((link) => {
                link.classList.toggle('is-active', link.dataset.questionPartLink === part);
            });

            partCards.forEach((card) => {
                card.classList.toggle('is-highlight', card.dataset.questionPart === part && !card.classList.contains('hidden'));
            });
        };

        partLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const part = link.dataset.questionPartLink;
                const target = visiblePartCard(part);

                if (!part || !target) {
                    return;
                }

                event.preventDefault();
                setActivePart(part);
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        const syncOptionValues = () => {
            optionList?.querySelectorAll('[data-option-row]').forEach((row, index) => {
                const optionInput = row.querySelector('[data-option-input]');
                const singleAnswer = row.querySelector('[data-single-answer]');
                const multipleAnswer = row.querySelector('[data-multiple-answer]');
                const placeholder = optionInput?.getAttribute('placeholder') || '';
                const value = optionInput?.value || '';

                if (singleAnswer) {
                    singleAnswer.value = value;
                }

                if (multipleAnswer) {
                    multipleAnswer.value = value;
                }

                if (optionInput && placeholder.includes('__INDEX__')) {
                    optionInput.setAttribute('placeholder', placeholder.replace('__INDEX__', String(index + 1)));
                }
            });
        };

        const setTypeMode = () => {
            const type = typeField?.value || 'single_choice';
            const isShort = type === 'short_answer';
            const isEssay = type === 'essay';
            const isMultiple = type === 'multiple_choice';
            const isTrueFalse = type === 'true_false';

            optionPanel?.classList.toggle('hidden', isShort || isEssay);
            shortAnswerPanel?.classList.toggle('hidden', !isShort);
            essayPanel?.classList.toggle('hidden', !isEssay);

            optionList?.querySelectorAll('[data-option-row]').forEach((row, index) => {
                const singleAnswer = row.querySelector('[data-single-answer]');
                const multipleAnswer = row.querySelector('[data-multiple-answer]');
                const optionInput = row.querySelector('[data-option-input]');
                const removeButton = row.querySelector('[data-remove-option]');

                singleAnswer?.classList.toggle('hidden', isMultiple);
                multipleAnswer?.classList.toggle('hidden', !isMultiple);

                if (isTrueFalse && optionInput) {
                    optionInput.value = index === 0 ? 'True' : (index === 1 ? 'False' : '');
                    optionInput.readOnly = index < 2;
                    row.classList.toggle('hidden', index > 1);
                } else {
                    if (optionInput) {
                        optionInput.readOnly = false;
                    }
                    row.classList.remove('hidden');
                }

                if (removeButton) {
                    removeButton.disabled = isTrueFalse || (optionList.querySelectorAll('[data-option-row]').length <= 2);
                    removeButton.classList.toggle('opacity-40', removeButton.disabled);
                }
            });

            addButton?.classList.toggle('hidden', isShort || isEssay || isTrueFalse);
            syncOptionValues();

            const activePart = builder.querySelector('[data-question-part-link].is-active')?.dataset.questionPartLink || 'content';
            setActivePart(activePart);
        };

        addButton?.addEventListener('click', () => {
            if (!template || !optionList) {
                return;
            }

            const fragment = template.content.cloneNode(true);
            optionList.append(fragment);
            setTypeMode();
        });

        optionList?.addEventListener('input', (event) => {
            if (event.target.matches('[data-option-input]')) {
                syncOptionValues();
            }
        });

        optionList?.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-option]');
            if (!removeButton || removeButton.disabled) {
                return;
            }

            removeButton.closest('[data-option-row]')?.remove();
            setTypeMode();
        });

        typeField?.addEventListener('change', setTypeMode);
        setTypeMode();
        setActivePart('content');
        renderQuestionIndex();

        if (questionIndex) {
            const indexObserver = new MutationObserver(renderQuestionIndex);
            indexObserver.observe(questionMain || builder, { childList: true, subtree: true });
        }
    });
});
