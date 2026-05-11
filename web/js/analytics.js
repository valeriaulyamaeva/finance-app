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
        currencySymbol,
        weekdayLabels,
        weekdayTotals,
        weekdayCounts
    } = analyticsData;

    const currency = currencySymbol || '₽';
    const formatValue = (val) => `${parseFloat(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currency}`;

    const isDark = document.body.classList.contains('theme-dark');

    // 24-color palette — pastel for light, muted for dark
    const PALETTE_LIGHT = [
        '#A8D5BA','#FFD3B6','#A8D8EA','#FFAAA5','#FFE4A8','#C9C9FF',
        '#B5EAD7','#FFDAC1','#C7CEEA','#FF9AA2','#E2F0CB','#B5CDA3',
        '#F1BBA8','#AEDDEF','#D6CDEA','#FFBE9F','#9BD0B0','#FF8B94',
        '#FFC8DD','#BDE0FE','#CDB4DB','#FFCFD2','#C5DEDD','#DBCDF0',
    ];
    const PALETTE_DARK = [
        '#6e9d83','#b89074','#7298ab','#b86b6b','#b89760','#8888b8',
        '#7eb09a','#b89072','#8a91b0','#b06f76','#a8b682','#8aa07a',
        '#a87f6e','#79a8b5','#9e93b0','#b88670','#71a085','#b86870',
        '#b88aa0','#8aaec9','#9082a8','#b88a8c','#8aabaa','#9c8eb0',
    ];
    const palette = isDark ? PALETTE_DARK : PALETTE_LIGHT;
    const borderColor = isDark ? '#1a1a20' : '#fff';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#a1a1a9' : '#4b5563';

    // Apply global Chart.js defaults so axes/legend follow theme
    if (typeof Chart !== 'undefined') {
        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;
        Chart.defaults.font.family = "'Inter', 'Poppins', system-ui, sans-serif";
    }

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categoryData.map(c => c.category),
            datasets: [{
                data: categoryData.map(c => parseFloat(c.total)),
                backgroundColor: palette,
                borderWidth: 2,
                borderColor: borderColor,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 14,
                        padding: 12,
                        color: textColor,
                        font: { size: 11 }
                    }
                },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${formatValue(ctx.raw)}` } }
            }
        }
    });

    // Semantic colors (muted for dark)
    const colorExpense = isDark ? '#b86b6b' : '#FF9AA2';
    const colorIncome  = isDark ? '#7eb09a' : '#bdcdab';
    const colorRemain  = isDark ? '#b89760' : '#efcd95';
    const colorAccent  = isDark ? '#8a91b0' : '#abb5e4';
    const rgba = (hex, a) => {
        const n = hex.replace('#', '');
        const r = parseInt(n.slice(0,2), 16), g = parseInt(n.slice(2,4), 16), b = parseInt(n.slice(4,6), 16);
        return `rgba(${r},${g},${b},${a})`;
    };

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Расходы',
                    data: expenseValues,
                    borderColor: colorExpense,
                    backgroundColor: (ctx) => {
                        const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                        gradient.addColorStop(0, rgba(colorExpense, 0.3));
                        gradient.addColorStop(1, rgba(colorExpense, 0.02));
                        return gradient;
                    },
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'Доходы',
                    data: incomeValues,
                    borderColor: colorIncome,
                    backgroundColor: (ctx) => {
                        const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                        gradient.addColorStop(0, rgba(colorIncome, 0.3));
                        gradient.addColorStop(1, rgba(colorIncome, 0.02));
                        return gradient;
                    },
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { color: textColor } },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: { label: ctx => `${ctx.dataset.label}: ${formatValue(ctx.raw)}` }
                }
            },
            interaction: { mode: 'nearest', axis: 'x', intersect: false },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor } },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { color: textColor, callback: (value) => formatValue(value) }
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
                backgroundColor: [colorIncome, colorExpense, colorRemain],
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
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { color: textColor, callback: (value) => formatValue(value) }
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
                backgroundColor: colorAccent,
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
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { color: textColor, callback: (value) => formatValue(value) }
                }
            }
        }
    });

    new Chart(document.getElementById('topCategoriesChart'), {
        type: 'pie',
        data: {
            labels: topCategories.map(t => t.category),
            datasets: [{
                data: topCategories.map(t => parseFloat(t.total)),
                backgroundColor: palette.slice(0, 5),
                borderColor: borderColor,
                borderWidth: 2,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 14, padding: 12, color: textColor, font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${formatValue(ctx.raw)}` } }
            }
        }
    });

    // Expenses by day of week
    if (weekdayLabels && weekdayLabels.length > 0) {
        const maxTotal = Math.max(...weekdayTotals);
        const peakColor = rgba(colorExpense, 0.85);
        const normalColor = rgba(colorAccent, 0.7);
        const weekdayColors = weekdayTotals.map(v =>
            v === maxTotal && v > 0 ? peakColor : normalColor
        );

        new Chart(document.getElementById('weekdayChart'), {
            type: 'bar',
            data: {
                labels: weekdayLabels,
                datasets: [{
                    label: 'Сумма расходов',
                    data: weekdayTotals,
                    backgroundColor: weekdayColors,
                    borderRadius: 8,
                    barPercentage: 0.7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const idx = ctx.dataIndex;
                                return `${formatValue(ctx.raw)} (${weekdayCounts[idx]} операций)`;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor } },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, callback: (value) => formatValue(value) }
                    }
                }
            }
        });
    }

});
