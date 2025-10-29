document.addEventListener('DOMContentLoaded', () => {
    const {
        categoryData,
        averageData,
        topCategories,
        totalBudget,
        totalSpent,
        remaining,
        months,
        expenseValues,
        incomeValues,
        currencySymbol
    } = analyticsData;

    const currency = currencySymbol || '₽';
    const formatValue = (val) => `${parseFloat(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currency}`;

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categoryData.map(c => c.category),
            datasets: [{
                data: categoryData.map(c => parseFloat(c.total)),
                backgroundColor: ['#bdcdab','#FFDAC1','#C7CEEA','#FF9AA2','#E2F0CB','#B5CDA3'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 20, padding: 15 } },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${formatValue(ctx.raw)}` } }
            }
        }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Расходы',
                    data: expenseValues,
                    borderColor: '#FF9AA2',
                    backgroundColor: (ctx) => {
                        const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                        gradient.addColorStop(0, 'rgba(255,154,162,0.3)');
                        gradient.addColorStop(1, 'rgba(255,154,162,0.05)');
                        return gradient;
                    },
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 7
                },
                {
                    label: 'Доходы',
                    data: incomeValues,
                    borderColor: '#bdcdab',
                    backgroundColor: (ctx) => {
                        const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                        gradient.addColorStop(0, 'rgba(181,234,215,0.4)');
                        gradient.addColorStop(1, 'rgba(181,234,215,0.05)');
                        return gradient;
                    },
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: { label: ctx => `${ctx.dataset.label}: ${formatValue(ctx.raw)}` }
                }
            },
            interaction: { mode: 'nearest', axis: 'x', intersect: false },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (value) => formatValue(value) }
                }
            }
        }
    });

    new Chart(document.getElementById('budgetChart'), {
        type: 'bar',
        data: {
            labels: ['Доход', 'Потрачено', 'Остаток'],
            datasets: [{
                data: [totalBudget, totalSpent, remaining],
                backgroundColor: ['#bdcdab', '#ec9ca3', '#efcd95'],
                borderRadius: 8,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${formatValue(ctx.raw)}` } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (value) => formatValue(value) }
                }
            }
        }
    });

    new Chart(document.getElementById('averageChart'), {
        type: 'bar',
        data: {
            labels: averageData.map(a => a.category),
            datasets: [{
                label: 'Средний чек',
                data: averageData.map(a => parseFloat(a.avg_amount)),
                backgroundColor: '#abb5e4',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${formatValue(ctx.raw)}` } }
            },
            scales: { y: { beginAtZero: true, ticks: { callback: (value) => formatValue(value) } } }
        }
    });

    new Chart(document.getElementById('topCategoriesChart'), {
        type: 'pie',
        data: {
            labels: topCategories.map(t => t.category),
            datasets: [{
                data: topCategories.map(t => parseFloat(t.total)),
                backgroundColor: ['#bdcdab','#FFDAC1','#C7CEEA','#FF9AA2','#E2F0CB'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 20, padding: 15 } },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${formatValue(ctx.raw)}` } }
            }
        }
    });
});
