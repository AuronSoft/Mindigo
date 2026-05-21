import '../../../../Core/src/resources/js/mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
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
        const typeField = builder.querySelector('[data-question-type]');
        const optionPanel = builder.querySelector('[data-option-panel]');
        const shortAnswerPanel = builder.querySelector('[data-short-answer-panel]');
        const optionList = builder.querySelector('[data-option-list]');
        const template = builder.querySelector('[data-option-template]');
        const addButton = builder.querySelector('[data-add-option]');

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
            const isMultiple = type === 'multiple_choice';
            const isTrueFalse = type === 'true_false';

            optionPanel?.classList.toggle('hidden', isShort);
            shortAnswerPanel?.classList.toggle('hidden', !isShort);

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

            addButton?.classList.toggle('hidden', isShort || isTrueFalse);
            syncOptionValues();
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
    });
});
