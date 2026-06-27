const trendData = window.__teacherTrend || {};
const performanceData = window.__teacherPerformance || {};

if (trendData.labels && typeof Chart !== 'undefined') {
    const el = document.getElementById('teacherTrendChart');
    if (el) {
        new Chart(el, {
            type: 'bar',
            data: {
                labels: trendData.labels,
                datasets: [{
                    data: trendData.counts,
                    backgroundColor: '#22c55e',
                    borderRadius: 8,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 8,
                        titleFont: { family: 'Be Vietnam Pro', weight: '800' },
                        bodyFont: { family: 'Be Vietnam Pro', weight: '700' },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 800 } } },
                    y: { border: { display: false }, grid: { color: '#e2e8f0' }, ticks: { color: '#94a3b8', font: { weight: 800 }, precision: 0 }, beginAtZero: true },
                },
            },
        });
    }
}

if (performanceData.labels && typeof Chart !== 'undefined') {
    const el = document.getElementById('teacherPerformanceChart');
    if (el) {
        new Chart(el, {
            type: 'bar',
            data: {
                labels: performanceData.labels,
                datasets: [
                    {
                        label: 'Điểm trung bình',
                        data: performanceData.averages,
                        backgroundColor: '#2563eb',
                        borderRadius: 8,
                        borderSkipped: false,
                    },
                    {
                        label: 'Số học sinh',
                        data: performanceData.studentCounts,
                        backgroundColor: '#93c5fd',
                        borderRadius: 8,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            color: '#64748b',
                            font: { family: 'Be Vietnam Pro', weight: 800 },
                        },
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 8,
                        titleFont: { family: 'Be Vietnam Pro', weight: '800' },
                        bodyFont: { family: 'Be Vietnam Pro', weight: '700' },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 800 } } },
                    y: { border: { display: false }, grid: { color: '#e2e8f0' }, ticks: { color: '#94a3b8', font: { weight: 800 }, precision: 0 }, beginAtZero: true },
                },
            },
        });
    }
}
