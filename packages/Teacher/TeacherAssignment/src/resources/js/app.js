document.querySelectorAll('[data-assignment-late-toggle]').forEach((input) => {
    const target = document.querySelector(input.dataset.assignmentLateToggle);

    if (!target) {
        return;
    }

    const sync = () => target.classList.toggle('hidden', !input.checked);
    input.addEventListener('change', sync);
    sync();
});

document.querySelectorAll('.js-assignment-files').forEach((input) => {
    const preview = document.getElementById(input.dataset.preview);
    const selectedFiles = [];

    const syncInput = () => {
        const transfer = new DataTransfer();
        selectedFiles.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    };

    const render = () => {
        if (!preview) {
            return;
        }

        preview.innerHTML = '';
        preview.classList.toggle('hidden', selectedFiles.length === 0);

        selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'relative min-w-0 rounded-xl border border-green-200 bg-green-50 px-3 py-2 pr-9 text-sm font-bold text-slate-700';
            item.innerHTML = '<span class="block truncate"></span><button type="button" class="absolute right-2 top-2 grid h-5 w-5 place-items-center rounded-full bg-white text-xs font-black text-red-600 shadow-sm hover:bg-red-50" aria-label="Bo file">&times;</button>';
            item.querySelector('span').textContent = file.name;
            item.querySelector('button').addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                syncInput();
                render();
            });
            preview.appendChild(item);
        });
    };

    input.addEventListener('change', () => {
        Array.from(input.files).forEach((file) => {
            const exists = selectedFiles.some((existing) => (
                existing.name === file.name
                && existing.size === file.size
                && existing.lastModified === file.lastModified
            ));

            if (!exists) {
                selectedFiles.push(file);
            }
        });

        syncInput();
        render();
    });
});

document.querySelectorAll('[data-remove-existing-file]').forEach((button) => {
    button.addEventListener('click', () => {
        const card = button.closest('[data-existing-file-card]');
        const checkbox = card?.querySelector('input[type="checkbox"]');

        if (!card || !checkbox) {
            return;
        }

        checkbox.checked = true;
        card.classList.add('hidden');
    });
});

document.querySelectorAll('.submission-type-card').forEach((card) => {
    card.addEventListener('click', function () {
        document.querySelectorAll('.submission-type-card').forEach((item) => {
            item.classList.remove('border-green-600', 'bg-green-50');
            item.classList.add('border-slate-200', 'bg-white');
        });

        this.classList.remove('border-slate-200', 'bg-white');
        this.classList.add('border-green-600', 'bg-green-50');
        this.querySelector('input[type="radio"]').checked = true;
    });
});
