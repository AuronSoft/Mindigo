import '../../../../Core/src/resources/js/mindigo-ui.js';
import './echo.js';

const dashboardMessages = window.__dashboardMessages || {};
const dashboardChartLabels = window.__dashboardChartLabels || {};
const dashboardRuntime = window.__dashboardRuntime || {};
const dashboardRanking = window.__dashboardRanking || {};

// ── Question Bank Chart ───────────────────────────────────────────────────
(() => {
    const stats    = window.__questionStats || {};
    const tabsEl   = document.getElementById('qchart-tabs');
    const barsEl   = document.getElementById('qchart-bars');
    const emptyEl  = document.getElementById('qchart-empty');

    if (!barsEl || !tabsEl) return;

    const renderBars = (tab) => {
        const items = stats[tab] || [];
        barsEl.innerHTML = '';

        if (!items.length || items.every(i => i.count === 0)) {
            barsEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
            emptyEl.classList.add('flex');
            return;
        }

        barsEl.classList.remove('hidden');
        emptyEl.classList.add('hidden');
        emptyEl.classList.remove('flex');

        const max = Math.max(...items.map(i => i.count), 1);

        items.forEach(item => {
            const pct = Math.max(8, Math.round((item.count / max) * 84));
            const col = document.createElement('div');
            col.className = 'flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2';
            col.innerHTML = `
                <span class="text-[10px] font-black text-slate-500">${item.count}</span>
                <div class="w-full rounded-t-2xl transition-all duration-500" style="height:${pct}%;background:${item.color}"></div>
                <span class="block w-full truncate text-center text-[10px] font-black text-slate-400" title="${item.label}">${item.label}</span>
            `;
            barsEl.appendChild(col);
        });
    };

    const setTab = (tab) => {
        tabsEl.querySelectorAll('[data-qchart-tab]').forEach(btn => {
            const active = btn.dataset.qchartTab === tab;
            btn.classList.toggle('rounded-full', active);
            btn.classList.toggle('bg-green-700', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('shadow-sm', active);
            btn.classList.toggle('text-slate-500', !active);
        });
        renderBars(tab);
    };

    tabsEl.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-qchart-tab]');
        if (btn) setTab(btn.dataset.qchartTab);
    });

    setTab('difficulty');
})();

// ── Quick Create Dropdown ─────────────────────────────────────────────────
(() => {
    const btn      = document.getElementById('quick-create-btn');
    const dropdown = document.getElementById('quick-create-dropdown');
    const iconPlus = document.getElementById('quick-create-icon');
    const iconX    = document.getElementById('quick-create-close-icon');

    if (!btn || !dropdown) return;

    const isOpen = () => btn.getAttribute('aria-expanded') === 'true';

    const open = () => {
        dropdown.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
        iconPlus.classList.add('hidden');
        iconX.classList.remove('hidden');
        btn.classList.replace('bg-green-600', 'bg-slate-900');
        btn.classList.replace('hover:bg-green-500', 'hover:bg-slate-800');
    };

    const close = () => {
        dropdown.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
        iconPlus.classList.remove('hidden');
        iconX.classList.add('hidden');
        btn.classList.replace('bg-slate-900', 'bg-green-600');
        btn.classList.replace('hover:bg-slate-800', 'hover:bg-green-500');
    };

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        isOpen() ? close() : open();
    });

    document.addEventListener('click', (e) => {
        if (!btn.closest('#quick-create-wrap').contains(e.target)) close();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
    });
})();

// ── Global Search ──────────────────────────────────────────────────────────
(() => {
    const cfg       = window.__searchConfig || {};
    const wrap      = document.getElementById('global-search-wrap');
    const input     = document.getElementById('global-search-input');
    const icon      = document.getElementById('global-search-icon');
    const spinner   = document.getElementById('global-search-spinner');
    const clearBtn  = document.getElementById('global-search-clear');
    const dropdown  = document.getElementById('global-search-results');
    const list      = document.getElementById('global-search-list');
    const empty     = document.getElementById('global-search-empty');

    if (!input || !dropdown) return;

    const typeIcon = {
        exam:     'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z',
        user:     'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z',
        question: 'M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H7l-4 4V7z',
        ticket:   'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
    };
    const typeTone = {
        exam:     'bg-green-100 text-green-700',
        user:     'bg-sky-100 text-sky-700',
        question: 'bg-amber-100 text-amber-700',
        ticket:   'bg-rose-100 text-rose-700',
    };

    let timer = null;
    let lastQuery = '';
    let focusedIdx = -1;
    let currentItems = [];

    const openDropdown  = () => dropdown.classList.remove('hidden');
    const closeDropdown = () => { dropdown.classList.add('hidden'); focusedIdx = -1; };
    const showSpinner   = (on) => { spinner.classList.toggle('hidden', !on); icon.classList.toggle('hidden', on); };

    const setFocus = (idx) => {
        const items = list.querySelectorAll('[data-search-item]');
        items.forEach((el, i) => el.classList.toggle('bg-slate-50', i === idx));
        focusedIdx = idx;
        if (items[idx]) items[idx].scrollIntoView({ block: 'nearest' });
    };

    const buildItem = (result, idx) => {
        const path = typeIcon[result.type] || typeIcon.exam;
        const tone = typeTone[result.type] || 'bg-slate-100 text-slate-600';
        const typeLabel = cfg.labels?.[result.type] || result.type;

        const el = document.createElement('a');
        el.href = result.url;
        el.className = 'flex items-center gap-3 px-4 py-2.5 no-underline transition hover:bg-slate-50';
        el.setAttribute('data-search-item', idx);
        el.innerHTML = `
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl ${tone}">
                <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="${path}"/>
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-black text-slate-900">${result.label}</span>
                <span class="block truncate text-xs font-bold text-slate-400">${result.sub || ''}</span>
            </span>
            <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500">${typeLabel}</span>
        `;
        return el;
    };

    const renderResults = (results) => {
        list.innerHTML = '';
        currentItems = results;
        focusedIdx = -1;

        if (!results.length) {
            list.classList.add('hidden');
            empty.classList.remove('hidden');
            empty.classList.add('flex');
            return;
        }

        list.classList.remove('hidden');
        empty.classList.add('hidden');
        empty.classList.remove('flex');

        results.forEach((r, i) => list.appendChild(buildItem(r, i)));
    };

    const doSearch = async (q) => {
        if (q === lastQuery) return;
        lastQuery = q;

        if (q.length < 2) { closeDropdown(); return; }

        showSpinner(true);
        openDropdown();

        try {
            const res = await fetch(`${cfg.url}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (input.value.trim() === q) renderResults(data.results || []);
        } catch {
            // silent fail
        } finally {
            showSpinner(false);
        }
    };

    input.addEventListener('input', () => {
        const q = input.value.trim();
        clearBtn.classList.toggle('hidden', !q);
        clearTimeout(timer);
        if (!q) { closeDropdown(); lastQuery = ''; return; }
        timer = setTimeout(() => doSearch(q), 300);
    });

    input.addEventListener('keydown', (e) => {
        const items = list.querySelectorAll('[data-search-item]');
        if (e.key === 'ArrowDown') { e.preventDefault(); setFocus(Math.min(focusedIdx + 1, items.length - 1)); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); setFocus(Math.max(focusedIdx - 1, 0)); }
        if (e.key === 'Enter' && focusedIdx >= 0 && items[focusedIdx]) {
            e.preventDefault();
            items[focusedIdx].click();
        }
        if (e.key === 'Escape') { closeDropdown(); input.blur(); }
    });

    clearBtn.addEventListener('click', () => {
        input.value = '';
        clearBtn.classList.add('hidden');
        closeDropdown();
        lastQuery = '';
        input.focus();
    });

    document.addEventListener('click', (e) => {
        if (!wrap.contains(e.target)) closeDropdown();
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && currentItems.length) openDropdown();
    });
})();

const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            padding: 10,
            titleFont: { family: 'Be Vietnam Pro', weight: '800' },
            bodyFont: { family: 'Be Vietnam Pro', weight: '700' },
        },
    },
    animation: { duration: 700, easing: 'easeInOutQuart' },
};

const initChart = (id, config) => {
    const element = document.getElementById(id);

    if (!element || typeof Chart === 'undefined') {
        return;
    }

    new Chart(element, config);
};

initChart('examTrendChart', {
    type: 'line',
    data: {
        labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
        datasets: [
            {
                data: [820, 940, 1080, 990, 1240, 1420, 1580],
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, .12)',
                borderWidth: 3,
                pointRadius: 0,
                tension: .38,
                fill: true,
            },
            {
                data: [620, 700, 760, 820, 910, 1040, 1130],
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22, 163, 74, .06)',
                borderWidth: 2,
                pointRadius: 0,
                tension: .38,
                fill: true,
            },
        ],
    },
    options: {
        ...chartDefaults,
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8', font: { weight: 800 } },
            },
            y: {
                border: { display: false },
                grid: { color: '#e2e8f0' },
                ticks: { color: '#94a3b8', font: { weight: 800 } },
            },
        },
    },
});

initChart('qualityChart', {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [72, 18, 10],
            backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
            borderWidth: 0,
            hoverOffset: 2,
        }],
    },
    options: {
        ...chartDefaults,
        cutout: '72%',
    },
});

initChart('rankingChart', {
    type: 'bar',
    data: {
        labels: dashboardRanking.labels || dashboardChartLabels.ranking || ['—', '—', '—', '—', '—'],
        datasets: [{
            data: dashboardRanking.data || [0, 0, 0, 0, 0],
            backgroundColor: ['#15803d', '#16a34a', '#22c55e', '#86efac', '#bbf7d0'],
            borderRadius: 14,
            borderSkipped: false,
            barThickness: 18,
        }],
    },
    options: {
        ...chartDefaults,
        indexAxis: 'y',
        scales: {
            x: {
                border: { display: false },
                grid: { color: '#e2e8f0' },
                ticks: { color: '#94a3b8', font: { weight: 800 } },
                max: 100,
            },
            y: {
                border: { display: false },
                grid: { display: false },
                ticks: { color: '#334155', font: { weight: 900 } },
            },
        },
    },
});

const sidebar = document.getElementById('sidebar');
const adminShell = document.getElementById('admin-shell');
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebarToggleIcon = document.getElementById('sidebar-toggle-icon');
const sidebarSearchInput = document.getElementById('sidebar-search-input');
const sidebarGroups = Array.from(document.querySelectorAll('[data-sidebar-group]'));
const sidebarTextItems = Array.from(document.querySelectorAll('[data-sidebar-text]'));
const avatarBtn = document.getElementById('sidebar-avatar-btn');
const userMenu = document.getElementById('user-menu');
const notificationBtn = document.getElementById('dashboard-notification-btn');
const notificationMenu = document.getElementById('dashboard-notification-menu');
const timeframeToggle = document.querySelector('[data-dashboard-timeframe-toggle]');
const timeframeTrack = document.querySelector('[data-dashboard-timeframe-track]');
const timeframeKnob = document.querySelector('[data-dashboard-timeframe-knob]');
const timeframeStatus = document.querySelector('[data-dashboard-timeframe-status]');
const dashboardDateButton = document.querySelector('[data-dashboard-date-button]');
const dashboardDateInput = document.querySelector('[data-dashboard-date-input]');
const dashboardDateLabel = document.querySelector('[data-dashboard-date-label]');
const dashboardDropdowns = Array.from(document.querySelectorAll('[data-dashboard-dropdown]'));

const normalizeSearchText = (value) => (
    (value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[\u0111\u0110]/g, 'd')
        .replace(/[^a-z0-9\s+&-]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
);

const isSidebarExpanded = () => sidebar?.dataset.expanded === 'true';

const getActiveSidebarItem = (group) => (
    group.querySelector('.sidebar-submenu-item.bg-green-50, .sidebar-submenu-item.text-green-700')
);

const setGroupOpen = (group, open) => {
    const submenu = group.querySelector('[data-sidebar-submenu]');
    submenu?.classList.toggle('hidden', !open);
    submenu?.classList.toggle('grid', open);
};

const setGroupActive = (group, active) => {
    const trigger = group.querySelector('.sidebar-group-trigger');
    const icon = trigger?.querySelector('span:first-child');

    trigger?.classList.toggle('bg-green-50', active);
    trigger?.classList.toggle('text-green-800', active);
    trigger?.classList.toggle('text-slate-700', !active);
    icon?.classList.toggle('bg-green-100', active);
    icon?.classList.toggle('text-green-600', active);
    icon?.classList.toggle('bg-slate-100', !active);
    icon?.classList.toggle('text-slate-600', !active);
};

const syncSidebar = () => {
    const expanded = isSidebarExpanded();
    const compactWidthClass = sidebar?.dataset.compactWidth || 'w-20';
    const compactGridClass = adminShell?.dataset.compactGrid || 'grid-cols-[5rem_minmax(0,1fr)]';
    const compactCenteredItems = sidebar?.querySelectorAll('[data-sidebar-compact-center]') || [];

    sidebar?.classList.toggle(compactWidthClass, !expanded);
    sidebar?.classList.toggle('w-72', expanded);
    adminShell?.classList.toggle(compactGridClass, !expanded);
    adminShell?.classList.toggle('grid-cols-[18rem_minmax(0,1fr)]', expanded);
    sidebarTextItems.forEach((item) => item.classList.toggle('hidden', !expanded));
    compactCenteredItems.forEach((item) => {
        item.classList.toggle('justify-center', !expanded);
        item.classList.toggle('px-0', !expanded);
        item.classList.toggle('px-3', expanded && item.id === 'sidebar-search-shell');
    });
    sidebarToggleIcon?.classList.toggle('rotate-180', expanded);

    sidebarGroups.forEach((group) => {
        const hasActiveItem = getActiveSidebarItem(group);
        const manuallyOpen = group.dataset.open === 'true';
        const hasVisibleSearch = Array.from(group.querySelectorAll('[data-sidebar-search-item]')).some((item) => !item.classList.contains('hidden'));
        const open = expanded && (manuallyOpen || Boolean(hasActiveItem) || Boolean(sidebarSearchInput?.value) && hasVisibleSearch);
        setGroupOpen(group, open);
        setGroupActive(group, open || Boolean(hasActiveItem));
    });
};

const filterSidebar = () => {
    if (!sidebarSearchInput) {
        return;
    }

    const keyword = normalizeSearchText(sidebarSearchInput.value);

    sidebarGroups.forEach((group) => {
        const groupSearchText = normalizeSearchText([
            group.dataset.groupName || '',
            group.querySelector('.sidebar-group-trigger')?.textContent || '',
            group.querySelector('.sidebar-group-trigger')?.getAttribute('title') || '',
        ].join(' '));
        const items = Array.from(group.querySelectorAll('[data-sidebar-search-item]'));
        const matchGroup = keyword.length > 0 && groupSearchText.includes(keyword);
        let visibleItems = 0;

        items.forEach((item) => {
            const itemSearchText = normalizeSearchText([
                item.dataset.searchLabel || '',
                item.textContent || '',
                group.dataset.groupName || '',
            ].join(' '));
            const showItem = !keyword || matchGroup || itemSearchText.includes(keyword);

            item.classList.toggle('hidden', !showItem);

            if (showItem) {
                visibleItems += 1;
            }
        });

        const hasActiveItem = getActiveSidebarItem(group);
        const open = isSidebarExpanded() && (Boolean(keyword)
            ? visibleItems > 0 || matchGroup
            : group.dataset.open === 'true' || Boolean(hasActiveItem));

        group.classList.toggle('hidden', Boolean(keyword) && !matchGroup && visibleItems === 0);
        setGroupOpen(group, open);
        setGroupActive(group, open || Boolean(hasActiveItem));
    });
};

const closeUserMenu = () => {
    userMenu?.classList.add('hidden');
};

const closeNotificationMenu = () => {
    notificationMenu?.classList.add('hidden');
    notificationBtn?.setAttribute('aria-expanded', 'false');
};

const closeDashboardDropdowns = (except = null) => {
    dashboardDropdowns.forEach((dropdown) => {
        if (dropdown === except) {
            return;
        }

        dropdown.querySelector('[data-dashboard-dropdown-menu]')?.classList.add('hidden');
        dropdown.querySelector('[data-dashboard-dropdown-trigger]')?.setAttribute('aria-expanded', 'false');
    });
};

const setDashboardToolFilter = (target, value) => {
    const panel = document.querySelector(`[data-dashboard-tool-panel="${target}"]`);

    if (!panel) {
        return;
    }

    if (target === 'source-list') {
        panel.querySelectorAll('[data-dashboard-source-row]').forEach((row) => {
            const visible = value === 'all' || row.dataset.filter === value;
            row.classList.toggle('hidden', !visible);
        });
    }

    if (target === 'source-chart') {
        panel.querySelectorAll('[data-dashboard-chart-bar]').forEach((bar) => {
            const active = value === 'all' || bar.dataset.filter === value;
            bar.classList.toggle('opacity-25', !active);
            bar.classList.toggle('grayscale', !active);
        });
    }
};

const setDashboardToolView = (target, value) => {
    const panel = document.querySelector(`[data-dashboard-tool-panel="${target}"]`);

    if (!panel) {
        return;
    }

    if (target === 'source-list') {
        panel.querySelectorAll('[data-dashboard-source-row]').forEach((row) => {
            row.classList.toggle('min-h-14', value !== 'compact');
            row.classList.toggle('min-h-11', value === 'compact');
        });
    }

    if (target === 'source-chart') {
        panel.querySelectorAll('[data-dashboard-chart-bar] > div').forEach((bar) => {
            bar.classList.toggle('rounded-2xl', value !== 'compare');
            bar.classList.toggle('rounded-t-2xl', value === 'compare');
        });
    }
};

const formatDashboardDate = (value) => {
    if (!value) {
        return '';
    }

    const [year, month, day] = value.split('-');
    return [day, month, year].filter(Boolean).join('/');
};

const formatDashboardDateTime = (date) => {
    const timeZone = dashboardRuntime.timezone || 'Asia/Ho_Chi_Minh';
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone,
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).formatToParts(date).reduce((items, part) => {
        items[part.type] = part.value;
        return items;
    }, {});

    return `${parts.day}/${parts.month}/${parts.year} ${parts.hour}:${parts.minute}:${parts.second}`;
};

const formatDashboardInputDate = (date) => {
    const timeZone = dashboardRuntime.timezone || 'Asia/Ho_Chi_Minh';
    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).formatToParts(date).reduce((items, part) => {
        items[part.type] = part.value;
        return items;
    }, {});

    return `${parts.year}-${parts.month}-${parts.day}`;
};

const startDashboardClock = () => {
    if (!dashboardDateLabel || !dashboardDateInput) {
        return;
    }

    const tick = () => {
        if (dashboardDateInput.dataset.manual === 'true') {
            return;
        }

        const now = new Date();
        dashboardDateLabel.textContent = formatDashboardDateTime(now);
        dashboardDateInput.value = formatDashboardInputDate(now);
    };

    tick();
    window.setInterval(tick, 1000);
};

const setTimeframeEnabled = (enabled) => {
    timeframeToggle?.setAttribute('aria-pressed', enabled ? 'true' : 'false');
    timeframeTrack?.classList.toggle('bg-green-600', enabled);
    timeframeTrack?.classList.toggle('bg-slate-300', !enabled);
    timeframeKnob?.classList.toggle('translate-x-3', enabled);
    timeframeStatus && (timeframeStatus.textContent = enabled
        ? (dashboardMessages.timeframe || 'Timeframe')
        : (dashboardMessages.date_filter_off || 'Date filter off'));
    dashboardDateButton?.classList.toggle('opacity-50', !enabled);
    dashboardDateButton?.classList.toggle('pointer-events-none', !enabled);
    dashboardDateInput && (dashboardDateInput.disabled = !enabled);
};

syncSidebar();
filterSidebar();

sidebarToggle?.addEventListener('click', () => {
    if (!sidebar) {
        return;
    }

    sidebar.dataset.expanded = isSidebarExpanded() ? 'false' : 'true';
    syncSidebar();

    if (isSidebarExpanded()) {
        sidebarSearchInput?.focus();
    } else {
        closeUserMenu();
    }
});

sidebarSearchInput?.addEventListener('input', filterSidebar);
sidebarSearchInput?.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        sidebarSearchInput.value = '';
        filterSidebar();
        syncSidebar();
    }

    if (event.key === 'Enter') {
        const firstVisibleItem = document.querySelector('[data-sidebar-search-item]:not(.hidden)');

        if (firstVisibleItem instanceof HTMLAnchorElement) {
            firstVisibleItem.click();
        }
    }
});

timeframeToggle?.addEventListener('click', () => {
    setTimeframeEnabled(timeframeToggle.getAttribute('aria-pressed') !== 'true');
});

dashboardDateButton?.addEventListener('click', () => {
    if (!dashboardDateInput || dashboardDateInput.disabled) {
        return;
    }

    if (typeof dashboardDateInput.showPicker === 'function') {
        dashboardDateInput.showPicker();
        return;
    }

    dashboardDateInput.focus();
    dashboardDateInput.click();
});

dashboardDateInput?.addEventListener('change', () => {
    if (dashboardDateLabel) {
        dashboardDateInput.dataset.manual = 'true';
        dashboardDateLabel.textContent = formatDashboardDate(dashboardDateInput.value);
    }
});

startDashboardClock();

dashboardDropdowns.forEach((dropdown) => {
    const trigger = dropdown.querySelector('[data-dashboard-dropdown-trigger]');
    const menu = dropdown.querySelector('[data-dashboard-dropdown-menu]');
    const label = dropdown.querySelector('[data-dashboard-dropdown-label]');

    trigger?.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = !menu?.classList.contains('hidden');

        closeDashboardDropdowns(dropdown);
        menu?.classList.toggle('hidden', isOpen);
        trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        closeUserMenu();
        closeNotificationMenu();
    });

    dropdown.querySelectorAll('[data-dashboard-option]').forEach((option) => {
        option.addEventListener('click', (event) => {
            event.stopPropagation();

            const target = option.dataset.target;
            const tool = option.dataset.tool;
            const value = option.dataset.value;

            dropdown.querySelectorAll('[data-dashboard-option]').forEach((item) => {
                item.classList.toggle('is-active', item === option);
            });

            if (label && !label.classList.contains('sr-only')) {
                label.textContent = option.textContent.trim();
            }

            if (tool === 'filter') {
                setDashboardToolFilter(target, value);
            }

            if (tool === 'view') {
                setDashboardToolView(target, value);
            }

            closeDashboardDropdowns();
            MindigoToast(dashboardMessages.tool_applied || 'Filter applied.', 'success', 1400);
        });
    });
});

sidebarGroups.forEach((group) => {
    const trigger = group.querySelector('.sidebar-group-trigger');

    trigger?.addEventListener('click', () => {
        if (!sidebar) {
            return;
        }

        sidebar.dataset.expanded = 'true';
        group.dataset.open = group.dataset.open === 'true' ? 'false' : 'true';

        sidebarGroups.forEach((otherGroup) => {
            if (otherGroup !== group && !getActiveSidebarItem(otherGroup)) {
                otherGroup.dataset.open = 'false';
            }
        });

        syncSidebar();
        closeUserMenu();
        closeNotificationMenu();
    });
});

avatarBtn?.addEventListener('click', (event) => {
    event.stopPropagation();
    closeNotificationMenu();

    if (!userMenu || !sidebar) {
        return;
    }

    if (!isSidebarExpanded()) {
        sidebar.dataset.expanded = 'true';
        syncSidebar();
    }

    if (!userMenu.classList.contains('hidden')) {
        closeUserMenu();
        return;
    }

    const avatarRect = avatarBtn.getBoundingClientRect();

    userMenu.classList.remove('hidden');
    userMenu.style.visibility = 'hidden';

    const menuHeight = userMenu.offsetHeight;
    const menuWidth = userMenu.offsetWidth;
    const sidebarRect = sidebar.getBoundingClientRect();
    const viewportPadding = 12;
    const top = Math.min(
        window.innerHeight - menuHeight - viewportPadding,
        Math.max(viewportPadding, avatarRect.top - menuHeight - 10),
    );
    const expandedLeft = sidebarRect.left + 12;
    const compactLeft = sidebarRect.right + 10;
    const maxLeft = window.innerWidth - menuWidth - viewportPadding;

    userMenu.style.top = `${top}px`;
    userMenu.style.left = `${Math.min(maxLeft, isSidebarExpanded() ? expandedLeft : compactLeft)}px`;
    userMenu.style.visibility = '';
});

notificationBtn?.addEventListener('click', (event) => {
    event.stopPropagation();
    closeUserMenu();

    const isOpen = !notificationMenu?.classList.contains('hidden');
    notificationMenu?.classList.toggle('hidden', isOpen);
    notificationBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
});

document.addEventListener('click', (event) => {
    if (userMenu && avatarBtn && !userMenu.contains(event.target) && !avatarBtn.contains(event.target)) {
        closeUserMenu();
    }

    if (notificationMenu && notificationBtn && !notificationMenu.contains(event.target) && !notificationBtn.contains(event.target)) {
        closeNotificationMenu();
    }

    closeDashboardDropdowns();
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    closeUserMenu();
    closeNotificationMenu();
    closeDashboardDropdowns();
});

document.querySelectorAll('[data-logout]').forEach((link) => {
    link.addEventListener('click', async (event) => {
        event.preventDefault();

        const confirmed = await MindigoConfirm({
            title: dashboardMessages.logout_title || 'Sign out',
            message: dashboardMessages.logout_message || 'Are you sure you want to sign out of the system?',
            confirmText: dashboardMessages.logout_confirm || 'Sign out',
            cancelText: dashboardMessages.logout_cancel || 'Cancel',
            type: 'warning',
        });

        if (!confirmed) {
            return;
        }

        MindigoToast(dashboardMessages.logging_out || 'Signing out...', 'info', 1200);

        const formId = link.dataset.logoutForm;
        const form = formId ? document.getElementById(formId) : null;
        setTimeout(() => form?.submit(), 500);
    });
});

(() => {
    const tooltip = document.getElementById('sidebar-tooltip');
    if (!tooltip || !sidebar) return;

    document.querySelectorAll('.sidebar-group-trigger, [data-sidebar-tooltip]').forEach((trigger) => {
        trigger.addEventListener('mouseenter', function () {
            if (isSidebarExpanded()) return;

            const label = this.dataset.sidebarTooltip || this.getAttribute('title');
            if (!label) return;

            const rect = this.getBoundingClientRect();
            tooltip.textContent = label;
            tooltip.style.top = `${rect.top + rect.height / 2}px`;
            tooltip.style.left = `${rect.right + 12}px`;
            tooltip.style.transform = 'translateY(-50%)';
            tooltip.classList.remove('hidden');
        });

        trigger.addEventListener('mouseleave', () => {
            tooltip.classList.add('hidden');
        });
    });
})();
