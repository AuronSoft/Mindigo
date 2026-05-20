// mindigo-ui.js - Global UI helpers

const MindigoIcons = {
    success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
    error: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
    warning: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    info: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
};

const MindigoColors = {
    success: { soft: '#dcfce7', border: '#bbf7d0', text: '#15803d', iconBg: '#22c55e' },
    error: { soft: '#fef2f2', border: '#fecaca', text: '#b91c1c', iconBg: '#ef4444' },
    danger: { soft: '#fef2f2', border: '#fecaca', text: '#b91c1c', iconBg: '#ef4444' },
    warning: { soft: '#fffbeb', border: '#fde68a', text: '#92400e', iconBg: '#f59e0b' },
    info: { soft: '#f0fdf4', border: '#bbf7d0', text: '#15803d', iconBg: '#22c55e' },
};

function escapeMindigoHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// TOAST NOTIFICATION
window.MindigoToast = function(msg, type = 'success', duration = 3500) {
    const toastType = MindigoColors[type] ? type : 'info';
    const c = MindigoColors[toastType];

    let container = document.getElementById('Mindigo-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'Mindigo-toast-container';
        container.style.cssText = `
            position: fixed;
            top: 22px;
            right: 22px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `
        width: min(380px, calc(100vw - 32px));
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) 18px;
        align-items: center;
        gap: 12px;
        background: #ffffff;
        border: 1px solid ${c.border};
        color: #0f172a;
        padding: 12px 14px;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
        font-family: 'Be Vietnam Pro', ui-sans-serif, system-ui, sans-serif;
        box-shadow: 0 18px 42px rgba(15,23,42,0.12);
        opacity: 0;
        transform: translateX(18px) scale(0.98);
        transition: opacity 0.25s ease, transform 0.25s ease;
        cursor: pointer;
        pointer-events: auto;
    `;

    toast.innerHTML = `
        <span style="
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: ${c.soft};
            color: ${c.text};
        ">${MindigoIcons[toastType]}</span>
        <span style="min-width:0; color:#334155;">${escapeMindigoHtml(msg)}</span>
        <span style="color:#cbd5e1; display:grid; place-items:center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
        </span>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0) scale(1)';
    }));

    const remove = () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(18px) scale(0.98)';
        setTimeout(() => toast.remove(), 250);
    };

    const timer = setTimeout(remove, duration);
    toast.addEventListener('click', () => {
        clearTimeout(timer);
        remove();
    });
};

function processMindigoToastNodes(root = document) {
    root.querySelectorAll('[data-Mindigo-toast-message]:not([data-Mindigo-toast-bound])').forEach((node) => {
        node.dataset.MindigoToastBound = '1';

        const message = node.dataset.MindigoToastMessage || node.textContent?.trim();
        if (!message) {
            return;
        }

        const type = node.dataset.MindigoToastType || 'info';
        const duration = Number(node.dataset.MindigoToastDuration || 3500);
        window.MindigoToast(message, type, Number.isFinite(duration) ? duration : 3500);
        node.remove();
    });
}

function getMindigoConfirmConfig(form) {
    return {
        title: form.dataset.MindigoConfirmTitle || 'Xac nhan',
        message: form.dataset.MindigoConfirmMessage || 'Ban co chac chan khong?',
        confirmText: form.dataset.MindigoConfirmText || 'Xac nhan',
        cancelText: form.dataset.MindigoConfirmCancel || 'Huy',
        type: form.dataset.MindigoConfirmType || 'warning',
    };
}

function bindMindigoConfirmForms() {
    if (document.__MindigoConfirmFormsBound) {
        return;
    }

    document.__MindigoConfirmFormsBound = true;

    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (!form.dataset.MindigoConfirmMessage) {
            return;
        }

        if (form.dataset.MindigoConfirmApproved === '1') {
            delete form.dataset.MindigoConfirmApproved;
            return;
        }

        event.preventDefault();

        const confirmed = await window.MindigoConfirm(getMindigoConfirmConfig(form));
        if (!confirmed) {
            return;
        }

        form.dataset.MindigoConfirmApproved = '1';

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit(event.submitter || undefined);
            return;
        }

        HTMLFormElement.prototype.submit.call(form);
    }, true);
}

function initMindigoUi() {
    bindMindigoConfirmForms();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => processMindigoToastNodes(), { once: true });
        return;
    }

    processMindigoToastNodes();
}

window.MindigoProcessToastNodes = processMindigoToastNodes;
window.MindigoBindConfirmForms = bindMindigoConfirmForms;

// CONFIRM DIALOG
window.MindigoConfirm = function({
    title = 'Xac nhan',
    message = 'Ban co chac chan khong?',
    confirmText = 'Xac nhan',
    cancelText = 'Huy',
    type = 'warning',
} = {}) {
    return new Promise((resolve) => {
        const confirmType = MindigoColors[type] ? type : 'warning';
        const c = MindigoColors[confirmType];
        const isDanger = confirmType === 'error' || confirmType === 'danger';
        const okBg = isDanger ? '#ef4444' : '#22c55e';
        const okShadow = isDanger ? '#b91c1c' : '#15803d';

        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            inset: 0;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15,23,42,0.38);
            backdrop-filter: blur(8px);
            opacity: 0;
            transition: opacity 0.2s ease;
        `;

        overlay.innerHTML = `
            <div style="
                width: min(420px, 100%);
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 22px;
                padding: 22px;
                box-shadow: 0 28px 70px rgba(15,23,42,0.22);
                transform: translateY(12px) scale(0.98);
                transition: transform 0.25s cubic-bezier(0.16,1,0.3,1);
                font-family: 'Be Vietnam Pro', ui-sans-serif, system-ui, sans-serif;
            ">
                <div style="display:flex; gap:14px; align-items:flex-start;">
                    <div style="
                        width: 46px;
                        height: 46px;
                        border-radius: 16px;
                        background: ${c.soft};
                        color: ${c.text};
                        display: grid;
                        place-items: center;
                        flex: 0 0 auto;
                        border: 1px solid ${c.border};
                    ">${MindigoIcons[confirmType] || MindigoIcons.warning}</div>

                    <div style="min-width:0; flex:1;">
                        <div style="font-size:18px; font-weight:900; color:#0f172a; line-height:1.35; margin-bottom:7px;">
                            ${escapeMindigoHtml(title)}
                        </div>
                        <div style="font-size:13.5px; color:#64748b; line-height:1.7; font-weight:600;">
                            ${escapeMindigoHtml(message)}
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:22px;">
                    <button id="Mindigo-confirm-cancel" type="button" style="
                        height: 42px;
                        padding: 0 18px;
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 14px;
                        color: #475569;
                        font-size: 13px;
                        font-weight: 900;
                        cursor: pointer;
                        font-family: inherit;
                        transition: background 0.18s ease, border-color 0.18s ease;
                    ">${escapeMindigoHtml(cancelText)}</button>

                    <button id="Mindigo-confirm-ok" type="button" style="
                        height: 42px;
                        padding: 0 20px;
                        background: ${okBg};
                        border: none;
                        border-radius: 14px;
                        color: #ffffff;
                        font-size: 13px;
                        font-weight: 900;
                        cursor: pointer;
                        font-family: inherit;
                        box-shadow: 0 4px 0 ${okShadow};
                        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
                    ">${escapeMindigoHtml(confirmText)}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const dialog = overlay.firstElementChild;
        const okButton = overlay.querySelector('#Mindigo-confirm-ok');
        const cancelButton = overlay.querySelector('#Mindigo-confirm-cancel');

        requestAnimationFrame(() => requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            dialog.style.transform = 'translateY(0) scale(1)';
        }));

        okButton.addEventListener('mouseenter', () => {
            okButton.style.transform = 'translateY(1px)';
            okButton.style.boxShadow = `0 2px 0 ${okShadow}`;
        });
        okButton.addEventListener('mouseleave', () => {
            okButton.style.transform = '';
            okButton.style.boxShadow = `0 4px 0 ${okShadow}`;
        });
        cancelButton.addEventListener('mouseenter', () => {
            cancelButton.style.background = '#f8fafc';
            cancelButton.style.borderColor = '#cbd5e1';
        });
        cancelButton.addEventListener('mouseleave', () => {
            cancelButton.style.background = '#ffffff';
            cancelButton.style.borderColor = '#e2e8f0';
        });

        const close = (result) => {
            overlay.style.opacity = '0';
            dialog.style.transform = 'translateY(10px) scale(0.98)';
            document.removeEventListener('keydown', keyHandler);
            setTimeout(() => {
                overlay.remove();
                resolve(result);
            }, 200);
        };

        const keyHandler = (event) => {
            if (event.key === 'Escape') {
                close(false);
            }

            if (event.key === 'Enter') {
                close(true);
            }
        };

        okButton.addEventListener('click', () => close(true));
        cancelButton.addEventListener('click', () => close(false));
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                close(false);
            }
        });
        document.addEventListener('keydown', keyHandler);
    });
};

initMindigoUi();
