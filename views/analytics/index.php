<?php
/** @var yii\web\View $this */
/** @var array $categoryData */
/** @var array $averageData */
/** @var array $topCategories */
/** @var float $totalBudget */
/** @var float $totalSpent */
/** @var float $remaining */
/** @var string $currencySymbol */
/** @var array $months */
/** @var array $expenseValues */
/** @var array $incomeValues */

use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = 'Аналитика';
$this->registerCssFile('@web/css/analytics.css');
$this->registerJsFile('@web/js/analytics.js', ['depends' => [yii\web\JqueryAsset::class]]);

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

<?php
$this->registerJs('
    const analyticsData = ' . json_encode([
        'categoryData' => $categoryData,
        'averageData' => $averageData,
        'topCategories' => $topCategories,
        'totalBudget' => $totalBudget,
        'totalSpent' => $totalSpent,
        'remaining' => $remaining,
        'months' => $months,
        'expenseValues' => $expenseValues,
        'incomeValues' => $incomeValues,
        'currencySymbol' => $currencySymbol,
    ]) . ';
', View::POS_HEAD);

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('@web/js/analytics.js', ['depends' => [JqueryAsset::class]]);
?>
