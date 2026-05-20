import '../../../../Core/src/resources/js/Mindigo-ui.js';

document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.querySelector('[data-audit-log-filter]');

    if (filterForm) {
        const keywordInput = filterForm.querySelector('[name="keyword"]');
        const instantFields = filterForm.querySelectorAll('select, input[type="date"]');

        instantFields.forEach((field) => {
            field.addEventListener('change', () => filterForm.requestSubmit());
        });

        keywordInput?.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            keywordInput.value = '';
            filterForm.requestSubmit();
        });
    }

    document.querySelectorAll('[data-audit-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = document.querySelector(button.dataset.auditCopy);
            const text = target?.textContent?.trim();

            if (!text) {
                MindigoToast('Không có dữ liệu để sao chép.', 'info');
                return;
            }

            try {
                await navigator.clipboard.writeText(text);
                MindigoToast('Đã sao chép dữ liệu.', 'success', 1800);
            } catch {
                MindigoToast('Trình duyệt không cho phép sao chép tự động.', 'warning');
            }
        });
    });

    highlightChangedJsonBlocks();
});

function highlightChangedJsonBlocks() {
    const oldBlock = document.querySelector('[data-audit-json="old"]');
    const newBlock = document.querySelector('[data-audit-json="new"]');

    if (!oldBlock || !newBlock) {
        return;
    }

    if (oldBlock.textContent?.trim() !== newBlock.textContent?.trim()) {
        oldBlock.classList.add('audit-log-diff-changed');
        newBlock.classList.add('audit-log-diff-changed');
    }
}
