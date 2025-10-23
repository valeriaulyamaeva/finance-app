<?php
/** @var yii\web\View $this */
/** @var array $categoryData */
/** @var array $monthlyData */
/** @var array $averageData */
/** @var array $topCategories */
/** @var float $totalBudget */
/** @var float $totalSpent */
/** @var float $remaining */
/** @var string $currencySymbol */

use yii\helpers\Html;

$this->title = 'Аналитика';
?>

<div class="analytics-page">
    <div class="sidebar">
        <h2>PastelFinance</h2>
        <ul>
            <li><a href="analytics">Аналитика</a></li>
            <li><a href="transaction">Транзакции</a></li>
            <li><a href="budget">Бюджеты</a></li>
            <li><a href="category">Категории</a></li>
            <li><a href="goal">Цели</a></li>
            <li><a href="settings">Настройки</a></li>
        </ul>
    </div>

    <div class="analytics-content">
        <h1>Аналитика и статистика</h1>

        <div class="summary-cards">
            <div class="summary-card">
                <h5>Общий бюджет</h5>
                <p><?= number_format($totalBudget, 2, '.', ' ') ?> <?= Html::encode($currencySymbol) ?></p>
            </div>
            <div class="summary-card">
                <h5>Потрачено</h5>
                <p><?= number_format($totalSpent, 2, '.', ' ') ?> <?= Html::encode($currencySymbol) ?></p>
            </div>
            <div class="summary-card">
                <h5>Остаток</h5>
                <p><?= number_format($remaining, 2, '.', ' ') ?> <?= Html::encode($currencySymbol) ?></p>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h4>Расходы по категориям</h4>
                <canvas id="categoryChart"></canvas>
            </div>

            <div class="chart-card">
                <h4>Расходы по месяцам</h4>
                <canvas id="monthlyChart"></canvas>
            </div>

            <div class="chart-card">
                <h4>Структура бюджета</h4>
                <canvas id="budgetChart"></canvas>
            </div>

            <div class="chart-card">
                <h4>Средний чек по категориям</h4>
                <canvas id="averageChart"></canvas>
            </div>

            <div class="chart-card">
                <h4>Топ 5 категорий</h4>
                <canvas id="topCategoriesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Расходы по категориям
    const categoryLabels = <?= json_encode(array_column($categoryData, 'category')) ?>;
    const categoryValues = <?= json_encode(array_map('floatval', array_column($categoryData, 'total'))) ?>;

    // Расходы и доходы по месяцам
    const monthlyLabels = <?= json_encode($months) ?>;
    const expenseValues = <?= json_encode($expenseValues) ?>;
    const incomeValues = <?= json_encode($incomeValues) ?>;

    // Средний чек по категориям
    const averageLabels = <?= json_encode(array_column($averageData, 'category')) ?>;
    const averageValues = <?= json_encode(array_map('floatval', array_column($averageData, 'avg_amount'))) ?>;

    // Топ 5 категорий
    const topLabels = <?= json_encode(array_column($topCategories, 'category')) ?>;
    const topValues = <?= json_encode(array_map('floatval', array_column($topCategories, 'total'))) ?>;

    // График "Расходы по категориям"
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryValues,
                backgroundColor: ['#a3c9c9', '#8da4a4', '#f2c94c', '#eb5757', '#6fcf97', '#56ccf2']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // График "Расходы и доходы по месяцам"
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [
                {
                    label: 'Расходы',
                    data: expenseValues,
                    borderColor: '#eb5757',
                    backgroundColor: 'rgba(235,87,87,0.2)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Доходы',
                    data: incomeValues,
                    borderColor: '#6fcf97',
                    backgroundColor: 'rgba(111,207,151,0.2)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // График "Структура бюджета"
    new Chart(document.getElementById('budgetChart'), {
        type: 'bar',
        data: {
            labels: ['Бюджет', 'Потрачено', 'Остаток'],
            datasets: [{
                data: [<?= $totalBudget ?>, <?= $totalSpent ?>, <?= $remaining ?>],
                backgroundColor: ['#6fcf97', '#eb5757', '#f2c94c']
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // График "Средний чек по категориям"
    new Chart(document.getElementById('averageChart'), {
        type: 'bar',
        data: {
            labels: averageLabels,
            datasets: [{
                label: 'Средний чек',
                data: averageValues,
                backgroundColor: '#a3c9c9'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // График "Топ 5 категорий"
    new Chart(document.getElementById('topCategoriesChart'), {
        type: 'pie',
        data: {
            labels: topLabels,
            datasets: [{
                data: topValues,
                backgroundColor: ['#6fcf97', '#f2c94c', '#56ccf2', '#eb5757', '#8da4a4']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
    body {
        font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        background: #f9f7f4;
        margin: 0;
        padding: 0;
        color: #4b453f;
    }
    .sidebar {
        width: 20rem;
        background-color: #b6b6b6;
        color: #8e8e8e;
        padding: 2rem 1rem;
        height: 100vh;
        position: fixed;
    }
    .sidebar h2 {
        font-size: 2.5rem;
        color: #2c2929;
        margin-bottom: 2rem;
        font-weight: 600;
    }
    .sidebar ul {
        list-style: none;
        padding: 0;
    }
    .sidebar ul li a {
        color: #1c1b1b;
        text-decoration: none;
        display: block;
        padding: 0.5rem 0;
        font-weight: 500;
    }
    .sidebar ul li a:hover { color: #535353; }

    .content h1 {
        font-size: 2.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .analytics-page {
        display: flex;
    }

    .analytics-content {
        flex: 1;
        margin-left: 10rem;
        padding: 2rem;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    @media (min-width: 1200px) {
        .charts-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .chart-card {
        background-color: var(--card-bg, #fff);
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: background-color 0.3s, color 0.3s;
    }

    .chart-card h4 {
        margin-bottom: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
    }

    canvas {
        max-width: 100%;
        height: 240px !important;
    }
</style>
