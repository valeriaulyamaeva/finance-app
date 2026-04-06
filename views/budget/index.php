<?php

use app\models\Budget;
use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Category;
use yii\web\JqueryAsset;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\models\Budget[] $budgets */
/** @var array $summary */
/** @var User $user */
/** @var string $selectedMonth */
/** @var string $selectedYear */

$this->title = 'Бюджеты';

$years = range(date('Y') - 2, date('Y') + 2);
$months = [
    '01' => 'Январь', '02' => 'Февраль', '03' => 'Март', '04' => 'Апрель',
    '05' => 'Май', '06' => 'Июнь', '07' => 'Июль', '08' => 'Август',
    '09' => 'Сентябрь', '10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь',
];

$userCurrency = $user->currency ?? 'BYN';
$currencySymbols = ['BYN' => 'Br', 'EUR' => '€', 'USD' => '$', 'RUB' => '₽'];
$currencySymbol = $currencySymbols[$userCurrency] ?? $userCurrency;

$this->registerCssFile('@web/css/budget.css');

$jsVars = [
    'createUrl' => Url::to(['budget/create']),
    'updateUrl' => Url::to(['budget/update']),
    'deleteUrl' => Url::to(['budget/delete']),
    'viewUrl' => Url::to(['budget/view']),
    'userCurrency' => $userCurrency,
    'currencySymbol' => $currencySymbol,
];
$this->registerJs('const budgetConfig = ' . json_encode($jsVars) . ';', View::POS_HEAD);
$this->registerJsFile('@web/js/budget.js', ['depends' => [JqueryAsset::class]]);
?>

    <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
        <i class="fas fa-bars fa-2x"></i>
    </button>

    <div class="budget-page">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-center d-lg-none">
                <h2>PastelFinance</h2>
                <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times fa-lg"></i></button>
            </div>
            <h2 class="d-none d-lg-block">PastelFinance</h2>
            <ul>
                <li><a href="/analytics">Аналитика</a></li>
                <li><a href="/transaction">Транзакции</a></li>
                <li><a href="/budget" class="active">Бюджеты</a></li>
                <li><a href="/category">Категории</a></li>
                <li><a href="/goal">Цели</a></li>
                <li><a href="/settings">Настройки</a></li>
            </ul>
        </div>

        <div class="budget-content" id="mainContent">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h1>Бюджеты</h1>

                <div class="filter-section d-flex gap-2 bg-white p-2 rounded-3 shadow-sm border">
                    <select id="filterMonth" class="form-select form-select-sm border-0 bg-light">
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filterYear" class="form-select form-select-sm border-0 bg-light">
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="summary-cards">
                <div class="summary-card">
                    <h5>Всего бюджет</h5>
                    <p><?= number_format($summary['total_budget'] ?? 0, 2) ?> <?= Html::encode($currencySymbol) ?></p>
                </div>
                <div class="summary-card">
                    <h5>Потрачено</h5>
                    <p style="color:#dc2626;"><?= number_format($summary['total_spent'] ?? 0, 2) ?> <?= Html::encode($currencySymbol) ?></p>
                </div>
                <div class="summary-card">
                    <h5>Остаток</h5>
                    <p style="color:#16a34a;"><?= number_format($summary['remaining'] ?? 0, 2) ?> <?= Html::encode($currencySymbol) ?></p>
                </div>
            </div>

            <div class="actions mb-4">
                <button class="btn-add" id="addBudgetBtn">Добавить бюджет</button>
            </div>

            <div class="cards-container">
                <?php if (!empty($budgets)): ?>
                    <?php foreach ($budgets as $budget): ?>
                        <?php
                        $percent = $budget->getProgressPercentage();
                        $progressColor = $percent < 70 ? '#16a34a' : ($percent < 100 ? '#facc15' : '#dc2626');
                        ?>
                        <div class="card" data-id="<?= $budget->id ?>">
                            <div class="card-body">
                                <h3><?= Html::encode($budget->name) ?></h3>
                                <p><strong>Категория:</strong> <?= Html::encode($budget->category->name ?? '-') ?></p>
                                <p><strong>Лимит:</strong> <?= number_format($budget->amount, 2) ?> <?= $budget->currency ?></p>
                                <p><strong>Потрачено:</strong> <?= number_format($budget->spent, 2) ?> <?= $budget->currency ?></p>

                                <div class="budget-progress-bar">
                                    <div class="budget-progress-fill" style="width: <?= $percent ?>%; background: <?= $progressColor ?>;"></div>
                                </div>
                                <p class="budget-progress-text">
                                    <?= $percent ?>% из <?= number_format($budget->amount, 2) ?> <?= $budget->currency ?>
                                </p>

                                <p><strong>Остаток:</strong> <?= number_format($budget->getRemainingAmount(), 2) ?> <?= $budget->currency ?></p>
                                <p><strong>Период:</strong> <?= Budget::getPeriods()[$budget->period] ?? $budget->period ?></p>
                                <p><strong>Срок:</strong> <?= $budget->start_date ?> → <?= $budget->end_date ?? '—' ?></p>
                            </div>
                            <div class="card-actions">
                                <button class="editBtn" data-id="<?= $budget->id ?>">✏️</button>
                                <button class="deleteBtn" data-id="<?= $budget->id ?>">🗑️</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="w-100 text-center py-5">
                        <p class="text-muted fs-4">Нет бюджетов за выбранный период</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?= $this->render('_budgetModal', [
    'categories' => ArrayHelper::map(Category::find()->all(), 'id', 'name'),
    'user' => $user,
]) ?>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>