import '../../../../Core/src/resources/js/mindigo-ui.js';

const reportRuntime = window.__reportRuntime || {};

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
    animation: { duration: 600, easing: 'easeInOutQuart' },
};

const initChart = (id, config) => {
    const element = document.getElementById(id);
    if (!element || typeof Chart === 'undefined') return;
    new Chart(element, config);
};

// Attempt trend line chart
if (window.__reportTrend) {
    const { labels, counts } = window.__reportTrend;
    initChart('trendChart', {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: counts,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, .12)',
                borderWidth: 2.5,
                pointRadius: 0,
                tension: .38,
                fill: true,
            }],
        },
        options: {
            ...chartDefaults,
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 800 }, maxTicksLimit: 7 } },
                y: { border: { display: false }, grid: { color: '#e2e8f0' }, ticks: { color: '#94a3b8', font: { weight: 800 } }, beginAtZero: true },
            },
        },
    });
}

// Score distribution bar chart
if (window.__reportScoreDist) {
    const { labels, data } = window.__reportScoreDist;
    initChart('scoreDistChart', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: ['#fca5a5', '#fcd34d', '#86efac', '#34d399', '#16a34a'],
                borderRadius: 10,
                borderSkipped: false,
            }],
        },
        options: {
            ...chartDefaults,
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 800 } } },
                y: { border: { display: false }, grid: { color: '#e2e8f0' }, ticks: { color: '#94a3b8', font: { weight: 800 } }, beginAtZero: true },
            },
        },
    });
}

// Subject doughnut chart
if (window.__reportSubjects) {
    const { labels, data } = window.__reportSubjects;
    initChart('subjectChart', {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: ['#22c55e', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'],
                borderWidth: 0,
                hoverOffset: 4,
            }],
        },
        options: {
            ...chartDefaults,
            plugins: { ...chartDefaults.plugins, legend: { display: true, position: 'right', labels: { font: { family: 'Be Vietnam Pro', weight: '700' }, boxWidth: 12 } } },
            cutout: '65%',
        },
    });
}

// Exam detail score distribution
if (window.__reportExamDist) {
    const { labels, data } = window.__reportExamDist;
    initChart('examScoreDistChart', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: ['#fca5a5', '#fcd34d', '#86efac', '#34d399', '#16a34a'],
                borderRadius: 10,
                borderSkipped: false,
            }],
        },
        options: {
            ...chartDefaults,
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 800 } } },
                y: { border: { display: false }, grid: { color: '#e2e8f0' }, ticks: { color: '#94a3b8', font: { weight: 800 } }, beginAtZero: true },
            },
        },
    });
}
