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
        incomeValues
    } = analyticsData;

    const categoryLabels = categoryData.map(c => c.category);
    const categoryValues = categoryData.map(c => parseFloat(c.total));

    const averageLabels = averageData.map(a => a.category);
    const averageValues = averageData.map(a => parseFloat(a.avg_amount));

    const topLabels = topCategories.map(t => t.category);
    const topValues = topCategories.map(t => parseFloat(t.total));

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryValues,
                backgroundColor: ['#6fcf97','#56ccf2','#f2c94c','#eb5757','#a3c9c9','#8da4a4'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 20, padding: 15 } },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw.toLocaleString()}₽` } }
            }
        }
    });

    const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Расходы',
                    data: expenseValues,
                    borderColor: '#eb5757',
                    backgroundColor: ctx => {
                        const gradient = ctx.chart.ctx.createLinearGradient(0,0,0,200);
                        gradient.addColorStop(0, 'rgba(235,87,87,0.4)');
                        gradient.addColorStop(1, 'rgba(235,87,87,0.05)');
                        return gradient;
                    },
                    tension: 0.3,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                {
                    label: 'Доходы',
                    data: incomeValues,
                    borderColor: '#6fcf97',
                    backgroundColor: ctx => {
                        const gradient = ctx.chart.ctx.createLinearGradient(0,0,0,200);
                        gradient.addColorStop(0, 'rgba(111,207,151,0.4)');
                        gradient.addColorStop(1, 'rgba(111,207,151,0.05)');
                        return gradient;
                    },
                    tension: 0.3,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            },
            interaction: { mode: 'nearest', axis: 'x', intersect: false },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1000 } } }
        }
    });

    new Chart(document.getElementById('budgetChart'), {
        type: 'bar',
        data: {
            labels: ['Бюджет','Потрачено','Остаток'],
            datasets: [{
                data: [totalBudget,totalSpent,remaining],
                backgroundColor: ['#6fcf97','#eb5757','#f2c94c'],
                borderRadius: 8,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw.toLocaleString()}₽` } } },
            scales: { y: { beginAtZero: true } }
        }
    });

    new Chart(document.getElementById('averageChart'), {
        type: 'bar',
        data: { labels: averageLabels, datasets: [{ label: 'Средний чек', data: averageValues, backgroundColor: '#a3c9c9', borderRadius: 6 }] },
        options: { responsive: true, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw.toLocaleString()}₽` } } }, scales: { y: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('topCategoriesChart'), {
        type: 'pie',
        data: { labels: topLabels, datasets: [{ data: topValues, backgroundColor: ['#6fcf97','#f2c94c','#56ccf2','#eb5757','#8da4a4'], borderColor:'#fff', borderWidth:2 }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 20, padding: 15 } }, tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw.toLocaleString()}₽` } } } }
    });
});
