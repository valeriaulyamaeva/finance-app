document.addEventListener('DOMContentLoaded', () => {
    if (!window.forecastConfig) return;
    const { timeline, currencySymbol } = window.forecastConfig;

    const isDark = document.body.classList.contains('theme-dark');
    const textColor = isDark ? '#a1a1a9' : '#4b5563';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const actualColor = isDark ? '#7eb09a' : '#8db4b4';
    const forecastColor = isDark ? '#8a91b0' : '#abb5e4';

    const fmt = (v) => `${parseFloat(v).toLocaleString(undefined, { maximumFractionDigits: 0 })} ${currencySymbol}`;

    const el = document.getElementById('balanceTimelineChart');
    if (!el || typeof Chart === 'undefined') return;

    new Chart(el, {
        type: 'line',
        data: {
            labels: timeline.labels,
            datasets: [
                {
                    label: 'Факт',
                    data: timeline.actual,
                    borderColor: actualColor,
                    backgroundColor: 'transparent',
                    tension: 0.3,
                    pointRadius: 4,
                    spanGaps: false,
                },
                {
                    label: 'Прогнозирование',
                    data: timeline.forecast,
                    borderColor: forecastColor,
                    backgroundColor: 'transparent',
                    borderDash: [6, 6],
                    tension: 0.3,
                    pointRadius: 4,
                    pointStyle: 'rectRot',
                    spanGaps: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { color: textColor } },
                tooltip: {
                    callbacks: { label: (ctx) => `${ctx.dataset.label}: ${fmt(ctx.raw)}` },
                },
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor } },
                y: {
                    grid: { color: gridColor },
                    ticks: { color: textColor, callback: (v) => fmt(v) },
                },
            },
        },
    });
});
