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
$this->registerCssFile('@web/css/notifications.css');
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@web/js/analytics.js', ['depends' => [JqueryAsset::class]]);
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', ['position' => View::POS_HEAD]);

?>

    <head>
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?> | PastelFinance</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

    <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
        <i class="fas fa-bars fa-2x"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header d-flex justify-content-between align-items-center d-lg-none">
            <h2>PastelFinance</h2>
            <button class="sidebar-close" id="sidebarClose">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <h2 class="d-none d-lg-block">PastelFinance</h2>

        <ul>
            <li><a href="/analytics" class="active">Аналитика</a></li>
                <li><a href="/forecast">Прогнозирование</a></li>
            <li><a href="/transaction">Транзакции</a></li>
            <li><a href="/budget">Бюджеты</a></li>
            <li><a href="/category">Категории</a></li>
            <li><a href="/goal">Цели</a></li>
            <li><a href="/import">Импорт выписки</a></li>
            <li><a href="/settings">Настройки</a></li>
        </ul>
    </div>

        <div class="analytics-content" id="mainContent">
            <h1>Аналитика и статистика</h1>

            <div class="period-selector">
                <a href="/analytics?period=month" class="period-btn <?= $period === 'month' ? 'active' : '' ?>">Месяц</a>
                <a href="/analytics?period=quarter" class="period-btn <?= $period === 'quarter' ? 'active' : '' ?>">Квартал</a>
                <a href="/analytics?period=year" class="period-btn <?= $period === 'year' ? 'active' : '' ?>">Год</a>
                <div class="period-custom">
                    <input type="date" id="customStart" value="<?= Html::encode($startDate) ?>">
                    <input type="date" id="customEnd" value="<?= Html::encode($endDate) ?>">
                    <button class="period-btn" id="applyCustomPeriod">Применить</button>
                </div>
            </div>

            <div class="summary-cards">
                <div class="summary-card">
                    <h5>Доход</h5>
                    <p><?= number_format($totalIncome, 2, '.', ' ') ?> <?= Html::encode($currencySymbol) ?></p>
                    <?php if ($incomeChange !== null): ?>
                        <span class="change-badge <?= $incomeChange >= 0 ? 'positive' : 'negative' ?>">
                            <?= $incomeChange >= 0 ? '+' : '' ?><?= $incomeChange ?>%
                        </span>
                    <?php endif; ?>
                </div>
                <div class="summary-card">
                    <h5>Расход</h5>
                    <p><?= number_format($totalExpense, 2, '.', ' ') ?> <?= Html::encode($currencySymbol) ?></p>
                    <?php if ($expenseChange !== null): ?>
                        <span class="change-badge <?= $expenseChange <= 0 ? 'positive' : 'negative' ?>">
                            <?= $expenseChange >= 0 ? '+' : '' ?><?= $expenseChange ?>%
                        </span>
                    <?php endif; ?>
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

                <div class="chart-card">
                    <h4>Расходы по дням недели</h4>
                    <canvas id="weekdayChart"></canvas>
                </div>
            </div>
        </div>

        <div id="notificationModal" class="notification-modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Уведомления</h4>
                    <button class="mark-all-read">Отметить все как прочитанные</button>
                    <span class="close">&times;</span>
                </div>
                <ul id="notificationList" class="notification-list"></ul>
            </div>
        </div>
    </div>

<?php
$this->registerJs('
    const analyticsData = ' . json_encode([
        'categoryData' => $categoryData,
        'averageData' => $averageData,
        'topCategories' => $topCategories,
        'totalBudget' => $totalIncome,
        'totalSpent' => $totalExpense,
        'remaining' => $remaining,
        'months' => $months,
        'expenseValues' => $expenseValues,
        'incomeValues' => $incomeValues,
        'currencySymbol' => $currencySymbol,
        'weekdayLabels' => $weekdayLabels,
        'weekdayTotals' => $weekdayTotals,
        'weekdayCounts' => $weekdayCounts,
    ]) . ';
', View::POS_HEAD);

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
$this->registerJs("
    document.getElementById('applyCustomPeriod')?.addEventListener('click', function() {
        var start = document.getElementById('customStart').value;
        var end = document.getElementById('customEnd').value;
        if (start && end) {
            window.location.href = '/analytics?period=custom&start=' + start + '&end=' + end;
        }
    });
", View::POS_READY);
?>