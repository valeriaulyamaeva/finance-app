<?php

use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Category;
use yii\web\JqueryAsset;
use yii\web\View;

/** @var yii\web\View $this */
/** @var array $budgetsWithDisplay */
/** @var array $summary */
/** @var User $user */

$this->title = 'Бюджеты';

$createUrl = Url::to(['budget/create']);
$updateUrl = Url::to(['budget/update']);
$deleteUrl = Url::to(['budget/delete']);
$viewUrl = Url::to(['budget/view']);

$currencySymbols = [
    'BYN' => 'Br',
    'EUR' => '€',
    'USD' => '$',
    'RUB' => '₽',
];
$userCurrency = $user->currency ?? 'BYN';
$currencySymbol = $currencySymbols[$userCurrency] ?? $userCurrency;

$this->registerCssFile('@web/css/budget.css');

$jsVars = [
    'createUrl' => $createUrl,
    'updateUrl' => $updateUrl,
    'deleteUrl' => $deleteUrl,
    'viewUrl' => $viewUrl,
    'userCurrency' => $userCurrency,
    'currencySymbol' => $currencySymbol,
];
$this->registerJs(
    'const budgetConfig = ' . json_encode($jsVars) . ';',
    View::POS_HEAD
);

$this->registerJsFile('@web/js/budget.js', ['depends' => [JqueryAsset::class]]);
?>

    <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
        <i class="fas fa-bars fa-2x"></i>
    </button>

    <div class="budget-page">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-center d-lg-none">
                <h2>PastelFinance</h2>
                <button class="sidebar-close" id="sidebarClose">
                    <i class="fas fa-times fa-lg"></i>
                </button>
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
            <h1>Бюджеты</h1>

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
                <button class="btn-add" id="addBudgetBtn" data-bs-toggle="modal" data-bs-target="#budgetModal">
                    Добавить бюджет
                </button>
            </div>

            <div class="cards-container">
                <?php if (!empty($budgetsWithDisplay)): ?>
                    <?php foreach ($budgetsWithDisplay as $budgetData): ?>
                        <?php
                        $budget = $budgetData['model'];
                        $rawAmount = $budget->amount;
                        $rawSpent = $budgetData['raw_spent'];
                        $percent = $rawAmount > 0 ? min(100, ($rawSpent / $rawAmount) * 100) : 0;
                        $progressColor = $percent < 70 ? '#16a34a' : ($percent < 100 ? '#facc15' : '#dc2626');
                        ?>
                        <div class="card" data-id="<?= $budget->id ?>">
                            <div class="card-body">
                                <h3><?= Html::encode($budget->name) ?></h3>
                                <p><strong>Категория:</strong> <?= Html::encode($budget->category->name ?? '-') ?></p>
                                <p><strong>Лимит:</strong> <?= Html::encode($budgetData['display_amount']) ?> <?= Html::encode($userCurrency) ?></p>
                                <p><strong>Потрачено:</strong> <?= Html::encode($budgetData['display_spent']) ?> <?= Html::encode($userCurrency) ?></p>

                                <div class="budget-progress-bar">
                                    <div class="budget-progress-fill" style="width: <?= $percent ?>%; background: <?= $progressColor ?>;"></div>
                                </div>
                                <p class="budget-progress-text">
                                    <?= number_format($percent, 1) ?>% из <?= number_format($rawAmount, 2) ?> <?= Html::encode($currencySymbol) ?>
                                </p>

                                <p><strong>Остаток:</strong> <?= Html::encode($budgetData['display_remaining']) ?> <?= Html::encode($userCurrency) ?></p>
                                <p><strong>Период:</strong> <?= Html::encode($budget->displayPeriod()) ?></p>
                                <p><strong>Срок:</strong> <?= Html::encode($budget->start_date) ?> → <?= Html::encode($budget->end_date ?? '—') ?></p>
                            </div>

                            <div class="card-actions">
                                <button class="editBtn" data-id="<?= $budget->id ?>">✏️</button>
                                <button class="deleteBtn" data-id="<?= $budget->id ?>">🗑️</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-5 fs-4">Нет созданных бюджетов</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?= $this->render('_budgetModal', [
    'categories' => ArrayHelper::map(Category::find()->all(), 'id', 'name'),
    'userCurrency' => $userCurrency,
]) ?>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>