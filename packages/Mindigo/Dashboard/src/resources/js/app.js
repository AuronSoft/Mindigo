import '../../../../Core/src/resources/js/Mindigo-ui.js';

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

const sidebar = document.getElementById('sidebar');
const adminShell = document.getElementById('admin-shell');
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebarToggleIcon = document.getElementById('sidebar-toggle-icon');
const sidebarSearchInput = document.getElementById('sidebar-search-input');
const sidebarGroups = Array.from(document.querySelectorAll('[data-sidebar-group]'));
const sidebarTextItems = Array.from(document.querySelectorAll('[data-sidebar-text]'));
const avatarBtn = document.getElementById('sidebar-avatar-btn');
const userMenu = document.getElementById('user-menu');

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

const setGroupOpen = (group, open) => {
    const submenu = group.querySelector('[data-sidebar-submenu]');
    submenu?.classList.toggle('hidden', !open);
    submenu?.classList.toggle('grid', open);
};

const syncSidebar = () => {
    const expanded = isSidebarExpanded();

    sidebar?.classList.toggle('w-20', !expanded);
    sidebar?.classList.toggle('w-72', expanded);
    adminShell?.classList.toggle('grid-cols-[5rem_minmax(0,1fr)]', !expanded);
    adminShell?.classList.toggle('grid-cols-[18rem_minmax(0,1fr)]', expanded);
    sidebarTextItems.forEach((item) => item.classList.toggle('hidden', !expanded));
    sidebarToggleIcon?.classList.toggle('rotate-180', expanded);

    sidebarGroups.forEach((group) => {
        const hasActiveItem = group.querySelector('.sidebar-submenu-item.bg-green-50, .sidebar-submenu-item.text-green-700');
        const hasVisibleSearch = Array.from(group.querySelectorAll('[data-sidebar-search-item]')).some((item) => !item.classList.contains('hidden'));
        const open = expanded && (Boolean(hasActiveItem) || Boolean(sidebarSearchInput?.value) && hasVisibleSearch);
        setGroupOpen(group, open);
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

        group.classList.toggle('hidden', Boolean(keyword) && !matchGroup && visibleItems === 0);
        setGroupOpen(group, isSidebarExpanded() && (Boolean(keyword) ? visibleItems > 0 || matchGroup : group.querySelector('.sidebar-submenu-item.bg-green-50, .sidebar-submenu-item.text-green-700')));
    });
};

const closeUserMenu = () => {
    userMenu?.classList.add('hidden');
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

avatarBtn?.addEventListener('click', (event) => {
    event.stopPropagation();

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

    const sidebarRect = sidebar.getBoundingClientRect();
    const avatarRect = avatarBtn.getBoundingClientRect();

    userMenu.classList.remove('hidden');
    userMenu.style.visibility = 'hidden';

    const menuHeight = userMenu.offsetHeight;
    userMenu.style.top = `${Math.max(12, avatarRect.top - menuHeight - 10)}px`;
    userMenu.style.left = `${sidebarRect.right + 12}px`;
    userMenu.style.visibility = '';
});

document.addEventListener('click', (event) => {
    if (!userMenu || !avatarBtn) {
        return;
    }

    if (!userMenu.contains(event.target) && !avatarBtn.contains(event.target)) {
        closeUserMenu();
    }
});

document.querySelectorAll('[data-logout]').forEach((link) => {
    link.addEventListener('click', async (event) => {
        event.preventDefault();

        const confirmed = await MindigoConfirm({
            title: 'Dang xuat',
            message: 'Ban co chac chan muon dang xuat khoi he thong khong?',
            confirmText: 'Dang xuat',
            cancelText: 'Huy',
            type: 'warning',
        });

        if (!confirmed) {
            return;
        }

        MindigoToast('Dang dang xuat...', 'info', 1200);

        const formId = link.dataset.logoutForm;
        const form = formId ? document.getElementById(formId) : null;
        setTimeout(() => form?.submit(), 500);
    });
});

(() => {
    const tooltip = document.getElementById('sidebar-tooltip');
    if (!tooltip || !sidebar) return;

    document.querySelectorAll('.sidebar-group-trigger').forEach((trigger) => {
        trigger.addEventListener('mouseenter', function () {
            if (isSidebarExpanded()) return;

            const label = this.getAttribute('title');
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
