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
            datasets: [{ data: categoryValues, backgroundColor: ['#a3c9c9','#8da4a4','#f2c94c','#eb5757','#6fcf97','#56ccf2'] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                { label: 'Расходы', data: expenseValues, borderColor: '#eb5757', backgroundColor: 'rgba(235,87,87,0.2)', tension: 0.3, fill: true },
                { label: 'Доходы', data: incomeValues, borderColor: '#6fcf97', backgroundColor: 'rgba(111,207,151,0.2)', tension: 0.3, fill: true }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('budgetChart'), {
        type: 'bar',
        data: { labels: ['Бюджет','Потрачено','Остаток'], datasets: [{ data: [totalBudget,totalSpent,remaining], backgroundColor: ['#6fcf97','#eb5757','#f2c94c'] }] },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('averageChart'), {
        type: 'bar',
        data: { labels: averageLabels, datasets: [{ label: 'Средний чек', data: averageValues, backgroundColor: '#a3c9c9' }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('topCategoriesChart'), {
        type: 'pie',
        data: { labels: topLabels, datasets: [{ data: topValues, backgroundColor: ['#6fcf97','#f2c94c','#56ccf2','#eb5757','#8da4a4'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
});
