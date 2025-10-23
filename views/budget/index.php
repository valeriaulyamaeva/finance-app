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

<div class="content">
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

    <button class="btn-add" id="addBudgetBtn" data-bs-toggle="modal" data-bs-target="#budgetModal">
        Добавить бюджет
    </button>

    <div class="cards-container">
        <?php if (!empty($budgetsWithDisplay)): ?>
            <?php foreach ($budgetsWithDisplay as $budgetData): ?>
                <?php $budget = $budgetData['model']; ?>
                <div class="card" data-id="<?= $budget->id ?>">
                    <div>
                        <h3><?= Html::encode($budget->name) ?></h3>
                        <p>Категория: <?= Html::encode($budget->category->name ?? '-') ?></p>
                        <p>Сумма: <?= Html::encode($budgetData['display_amount']) ?> <?= Html::encode($budgetData['display_currency'] ?? $currencySymbol) ?></p>
                        <p>Потрачено: <?= Html::encode($budgetData['display_spent']) ?> <?= Html::encode($budgetData['display_currency']) ?></p>
                        <p>Остаток: <?= Html::encode($budgetData['display_remaining']) ?> <?= Html::encode($budgetData['display_currency']) ?></p>
                        <p>Период: <?= Html::encode($budget->displayPeriod()) ?></p>
                        <p>Срок: <?= Html::encode($budget->start_date) ?> → <?= Html::encode($budget->end_date) ?></p>
                    </div>
                    <div class="actions">
                        <button class="editBtn" data-id="<?= $budget->id ?>">✏️</button>
                        <button class="deleteBtn" data-id="<?= $budget->id ?>">🗑️</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет доступных бюджетов.</p>
        <?php endif; ?>
    </div>
</div>

<?= $this->render('_budgetModal', [
    'categories' => ArrayHelper::map(Category::find()->all(), 'id', 'name'),
    'userCurrency' => $userCurrency,
]) ?>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [
    'depends' => [JqueryAsset::class],
]);
?>

