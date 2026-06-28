// Global UI helpers.

const MindigoIcons = {
    success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
    error: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
    warning: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    info: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
};

const MindigoColors = {
    success: { soft: '#dcfce7', border: '#bbf7d0', text: '#15803d' },
    error: { soft: '#fef2f2', border: '#fecaca', text: '#b91c1c' },
    danger: { soft: '#fef2f2', border: '#fecaca', text: '#b91c1c' },
    warning: { soft: '#fffbeb', border: '#fde68a', text: '#92400e' },
    info: { soft: '#f0fdf4', border: '#bbf7d0', text: '#15803d' },
};

function escapeMindigoHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function readMindigoData(element, key, fallback = '') {
    const normalKey = `mindigo${key.charAt(0).toUpperCase()}${key.slice(1)}`;
    const legacyKey = `Mindigo${key.charAt(0).toUpperCase()}${key.slice(1)}`;

    return element.dataset[normalKey] ?? element.dataset[legacyKey] ?? fallback;
}

function writeMindigoData(element, key, value) {
    element.dataset[`mindigo${key.charAt(0).toUpperCase()}${key.slice(1)}`] = value;
}

window.MindigoToast = function(message, type = 'success', duration = 3500) {
    const toastType = MindigoColors[type] ? type : 'info';
    const color = MindigoColors[toastType];

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
        border: 1px solid ${color.border};
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
        <span style="width:34px;height:34px;border-radius:12px;display:grid;place-items:center;background:${color.soft};color:${color.text};">${MindigoIcons[toastType]}</span>
        <span style="min-width:0;color:#334155;">${escapeMindigoHtml(message)}</span>
        <span style="color:#cbd5e1;display:grid;place-items:center;">
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
    root.querySelectorAll('[data-mindigo-toast-message]:not([data-mindigo-toast-bound]), [data-Mindigo-toast-message]:not([data-Mindigo-toast-bound])').forEach((node) => {
        writeMindigoData(node, 'toastBound', '1');

        const message = readMindigoData(node, 'toastMessage', node.textContent?.trim());
        if (!message) {
            return;
        }

        const type = readMindigoData(node, 'toastType', 'info');
        const duration = Number(readMindigoData(node, 'toastDuration', 3500));
        window.MindigoToast(message, type, Number.isFinite(duration) ? duration : 3500);
        node.remove();
    });
}

function getMindigoConfirmConfig(form) {
    return {
        title: readMindigoData(form, 'confirmTitle', 'Confirm action'),
        message: readMindigoData(form, 'confirmMessage', 'Are you sure you want to continue?'),
        confirmText: readMindigoData(form, 'confirmText', 'Confirm'),
        cancelText: readMindigoData(form, 'confirmCancel', 'Cancel'),
        type: readMindigoData(form, 'confirmType', 'warning'),
    };
}

function bindMindigoConfirmForms() {
    if (document.__MindigoConfirmFormsBound) {
        return;
    }

    document.__MindigoConfirmFormsBound = true;

    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !readMindigoData(form, 'confirmMessage')) {
            return;
        }

        if (readMindigoData(form, 'confirmApproved') === '1') {
            delete form.dataset.mindigoConfirmApproved;
            delete form.dataset.MindigoConfirmApproved;
            return;
        }

        event.preventDefault();

        const confirmed = await window.MindigoConfirm(getMindigoConfirmConfig(form));
        if (!confirmed) {
            return;
        }

        writeMindigoData(form, 'confirmApproved', '1');

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit(event.submitter || undefined);
            return;
        }

        HTMLFormElement.prototype.submit.call(form);
    }, true);
}

window.MindigoProcessToastNodes = processMindigoToastNodes;
window.MindigoBindConfirmForms = bindMindigoConfirmForms;

window.MindigoOpenModal = function(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
};

window.MindigoCloseModal = function(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.style.display = '';
    modal.setAttribute('aria-hidden', 'true');
};

function bindMindigoModals() {
    if (document.__MindigoModalsBound) {
        return;
    }

    document.__MindigoModalsBound = true;

    document.addEventListener('click', (event) => {
        const openTrigger = event.target.closest('[data-mindigo-modal-open]');
        if (openTrigger) {
            event.preventDefault();
            window.MindigoOpenModal(openTrigger.dataset.mindigoModalOpen);
            return;
        }

        const closeTrigger = event.target.closest('[data-mindigo-modal-close]');
        if (closeTrigger) {
            event.preventDefault();
            const target = closeTrigger.dataset.mindigoModalClose || closeTrigger.closest('[data-mindigo-modal]')?.id;
            window.MindigoCloseModal(target);
            return;
        }

        const modal = event.target.closest('[data-mindigo-modal]');
        if (modal && event.target === modal) {
            window.MindigoCloseModal(modal.id);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('[data-mindigo-modal]:not(.hidden)').forEach((modal) => {
            window.MindigoCloseModal(modal.id);
        });
    });
}

function getMindigoDrawer(id) {
    if (!id) {
        return {};
    }

    const overlay = Array.from(document.querySelectorAll('[data-mindigo-drawer]'))
        .find((element) => element.dataset.mindigoDrawer === id);
    const panel = Array.from(document.querySelectorAll('[data-mindigo-drawer-panel]'))
        .find((element) => element.dataset.mindigoDrawerPanel === id);

    return { overlay, panel };
}

window.MindigoOpenDrawer = function(id) {
    const { overlay, panel } = getMindigoDrawer(id);

    if (!overlay || !panel) {
        return;
    }

    overlay.classList.remove('hidden');

    requestAnimationFrame(() => {
        overlay.classList.remove('opacity-0');
        panel.style.transform = 'translateX(0)';
    });
};

window.MindigoCloseDrawer = function(id) {
    const { overlay, panel } = getMindigoDrawer(id);

    if (!overlay || !panel) {
        return;
    }

    overlay.classList.add('opacity-0');
    panel.style.transform = 'translateX(100%)';
    window.setTimeout(() => overlay.classList.add('hidden'), 180);
};

function bindMindigoDrawers() {
    if (document.__MindigoDrawersBound) {
        return;
    }

    document.__MindigoDrawersBound = true;

    document.addEventListener('click', (event) => {
        const openTrigger = event.target.closest('[data-mindigo-drawer-open]');
        if (openTrigger) {
            event.preventDefault();
            window.MindigoOpenDrawer(openTrigger.dataset.mindigoDrawerOpen);
            return;
        }

        const closeTrigger = event.target.closest('[data-mindigo-drawer-close]');
        if (closeTrigger) {
            event.preventDefault();
            window.MindigoCloseDrawer(closeTrigger.dataset.mindigoDrawerClose);
            return;
        }

        const overlay = event.target.closest('[data-mindigo-drawer]');
        if (overlay && event.target === overlay) {
            window.MindigoCloseDrawer(overlay.dataset.mindigoDrawer);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('[data-mindigo-drawer]:not(.hidden)').forEach((overlay) => {
            window.MindigoCloseDrawer(overlay.dataset.mindigoDrawer);
        });
    });
}

function bindMindigoAutoSubmit() {
    if (document.__MindigoAutoSubmitBound) {
        return;
    }

    document.__MindigoAutoSubmitBound = true;

    document.addEventListener('change', (event) => {
        const field = event.target.closest('[data-mindigo-auto-submit]');

        if (!field) {
            return;
        }

        field.form?.submit();
    });
}

function bindMindigoUrlSelects() {
    if (document.__MindigoUrlSelectsBound) {
        return;
    }

    document.__MindigoUrlSelectsBound = true;

    document.addEventListener('change', (event) => {
        const field = event.target.closest('[data-mindigo-select-url]');

        if (!field || !field.value) {
            return;
        }

        window.location.href = field.value;
    });
}

function bindMindigoTabs() {
    if (document.__MindigoTabsBound) {
        return;
    }

    document.__MindigoTabsBound = true;

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-mindigo-tab-target]');

        if (!trigger) {
            return;
        }

        event.preventDefault();

        const group = trigger.closest('[data-mindigo-tabs]');
        const tab = trigger.dataset.mindigoTabTarget;

        if (!group || !tab) {
            return;
        }

        group.querySelectorAll('[data-mindigo-tab-target]').forEach((button) => {
            const active = button === trigger;
            const activeClasses = (button.dataset.mindigoTabActiveClass || '').split(/\s+/).filter(Boolean);
            const inactiveClasses = (button.dataset.mindigoTabInactiveClass || '').split(/\s+/).filter(Boolean);

            button.classList.toggle('active', active);
            activeClasses.forEach((className) => button.classList.toggle(className, active));
            inactiveClasses.forEach((className) => button.classList.toggle(className, !active));
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        group.querySelectorAll('[data-mindigo-tab-panel]').forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.mindigoTabPanel !== tab);
        });

        if (trigger.dataset.mindigoTabSyncUrl === 'true') {
            const url = new URL(window.location.href);
            url.searchParams.set(trigger.dataset.mindigoTabParam || 'tab', tab);
            window.history.replaceState(null, '', url);
        }
    });
}

function bindMindigoValueSetters() {
    if (document.__MindigoValueSettersBound) {
        return;
    }

    document.__MindigoValueSettersBound = true;

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-mindigo-set-value]');

        if (!trigger) {
            return;
        }

        event.preventDefault();

        const scope = trigger.dataset.mindigoSetScope
            ? trigger.closest(trigger.dataset.mindigoSetScope)
            : document;
        const target = scope?.querySelector(trigger.dataset.mindigoSetTarget || 'input');

        if (target) {
            target.value = trigger.dataset.mindigoSetValue ?? '';
            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
}

window.MindigoConfirm = function({
    title = 'Confirm action',
    message = 'Are you sure you want to continue?',
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    type = 'warning',
} = {}) {
    return new Promise((resolve) => {
        const confirmType = MindigoColors[type] ? type : 'warning';
        const color = MindigoColors[confirmType];
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
            <div style="width:min(420px,100%);background:#ffffff;border:1px solid #e2e8f0;border-radius:22px;padding:22px;box-shadow:0 28px 70px rgba(15,23,42,0.22);transform:translateY(12px) scale(0.98);transition:transform 0.25s cubic-bezier(0.16,1,0.3,1);font-family:'Be Vietnam Pro',ui-sans-serif,system-ui,sans-serif;">
                <div style="display:flex;gap:14px;align-items:flex-start;">
                    <div style="width:46px;height:46px;border-radius:16px;background:${color.soft};color:${color.text};display:grid;place-items:center;flex:0 0 auto;border:1px solid ${color.border};">${MindigoIcons[confirmType] || MindigoIcons.warning}</div>
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:18px;font-weight:900;color:#0f172a;line-height:1.35;margin-bottom:7px;">${escapeMindigoHtml(title)}</div>
                        <div style="font-size:13.5px;color:#64748b;line-height:1.7;font-weight:600;">${escapeMindigoHtml(message)}</div>
                    </div>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:22px;">
                    <button id="Mindigo-confirm-cancel" type="button" style="height:42px;padding:0 18px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;color:#475569;font-size:13px;font-weight:900;cursor:pointer;font-family:inherit;transition:background 0.18s ease,border-color 0.18s ease;">${escapeMindigoHtml(cancelText)}</button>
                    <button id="Mindigo-confirm-ok" type="button" style="height:42px;padding:0 20px;background:${okBg};border:none;border-radius:14px;color:#ffffff;font-size:13px;font-weight:900;cursor:pointer;font-family:inherit;box-shadow:0 4px 0 ${okShadow};transition:transform 0.18s ease,box-shadow 0.18s ease,background 0.18s ease;">${escapeMindigoHtml(confirmText)}</button>
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
            if (event.key === 'Escape') close(false);
            if (event.key === 'Enter') close(true);
        };

        okButton.addEventListener('click', () => close(true));
        cancelButton.addEventListener('click', () => close(false));
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) close(false);
        });
        document.addEventListener('keydown', keyHandler);
    });
};

function initMindigoUi() {
    bindMindigoConfirmForms();
    bindMindigoModals();
    bindMindigoDrawers();
    bindMindigoAutoSubmit();
    bindMindigoUrlSelects();
    bindMindigoTabs();
    bindMindigoValueSetters();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => processMindigoToastNodes(), { once: true });
        return;
    }

    processMindigoToastNodes();
}

initMindigoUi();
