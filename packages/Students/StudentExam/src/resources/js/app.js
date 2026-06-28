const setActiveQuestion = (index) => {
    document.querySelectorAll('[data-question-item]').forEach((item) => {
        item.classList.toggle('hidden', item.dataset.questionItem !== String(index));
    });

    document.querySelectorAll('[data-question-nav-button]').forEach((button) => {
        const active = button.dataset.questionNavButton === String(index);
        button.classList.toggle('bg-blue-600', active);
        button.classList.toggle('text-white', active);
        button.classList.toggle('border-blue-600', active);
    });
};

document.addEventListener('click', async (event) => {
    const navButton = event.target.closest('[data-question-nav-button]');
    if (navButton) {
        setActiveQuestion(navButton.dataset.questionNavButton);
        return;
    }

    const submitButton = event.target.closest('[data-student-exam-submit]');
    if (!submitButton) {
        return;
    }

    const form = document.getElementById(submitButton.dataset.studentExamSubmit || 'exam-form');
    const confirmed = await window.MindigoConfirm?.({
        title: submitButton.dataset.confirmTitle || 'Submit exam',
        message: submitButton.dataset.confirmMessage || 'Are you sure you want to submit?',
        confirmText: submitButton.dataset.confirmText || 'Submit',
        cancelText: submitButton.dataset.confirmCancel || 'Cancel',
        type: 'warning',
    });

    if (confirmed) {
        form?.submit();
    }
});

const tabLeaveInput = document.getElementById('tab_leave_count');
if (tabLeaveInput) {
    let tabLeaveCount = Number(tabLeaveInput.value || 0);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            return;
        }

        tabLeaveCount += 1;
        tabLeaveInput.value = String(tabLeaveCount);
    });
}

document.querySelectorAll('[data-student-exam-timer]').forEach((timer) => {
    const expiresAt = Number(timer.dataset.expiresAt);
    const resultUrl = timer.dataset.resultUrl;
    const expiredMessage = timer.dataset.expiredMessage || 'Time expired.';
    const output = timer.querySelector('[data-student-exam-time-remaining]');

    if (!expiresAt || !output) {
        return;
    }

    const updateTimer = () => {
        const diff = Math.max(0, expiresAt - Date.now());
        const minutes = Math.floor(diff / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);

        output.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        if (diff > 0) {
            return;
        }

        clearInterval(timerInterval);
        window.MindigoToast?.(expiredMessage, 'warning', 2500);

        if (resultUrl) {
            window.setTimeout(() => {
                window.location.href = resultUrl;
            }, 900);
        }
    };

    const timerInterval = window.setInterval(updateTimer, 1000);
    updateTimer();
});
