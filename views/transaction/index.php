<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $summary */
/** @var array $goals */

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = 'Транзакции';
AppAsset::register($this);

$currencySymbols = ['BYN'=>'Br','USD'=>'$','EUR'=>'€', 'RUB' => '₽'];
$userCurrency = Yii::$app->user->identity->currency ?? 'BYN';

$urls = [
    'create' => Url::to(['transaction/create']),
    'update' => Url::to(['transaction/update']),
    'delete' => Url::to(['transaction/delete']),
    'view' => Url::to(['transaction/view']),
    'createRecurring' => Url::to(['recurring-transaction/create']),
];

$this->registerJs('const transactionConfig = ' . json_encode([
        'urls' => $urls,
        'currencySymbols' => $currencySymbols,
        'userCurrency' => $userCurrency,
    ]) . ';', View::POS_HEAD);

$this->registerCssFile('@web/css/transaction.css');
$this->registerJsFile('@web/js/transaction.js', ['depends' => [JqueryAsset::class]]);
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
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="summary-cards">
        <div class="summary-card">
            <h5>Доход</h5>
            <p style="color:#16a34a;"><?= number_format($summary['income'] ?? 0, 2) ?> <?= $currencySymbols[$userCurrency] ?? '' ?></p>
        </div>
        <div class="summary-card">
            <h5>Остаток с прошлого месяца</h5>
            <p><?= number_format($summary['previousBalance'] ?? 0, 2) ?> <?= $currencySymbols[$userCurrency] ?? '' ?></p>
        </div>
        <div class="summary-card">
            <h5>Расход</h5>
            <p style="color:#dc2626;"><?= number_format($summary['expense'] ?? 0, 2) ?> <?= $currencySymbols[$userCurrency] ?? '' ?></p>
        </div>
        <div class="summary-card">
            <h5>Баланс</h5>
            <p><?= number_format($summary['balance'] ?? 0, 2) ?> <?= $currencySymbols[$userCurrency] ?? '' ?></p>
        </div>
    </div>

    <button class="btn-add" id="createTransactionBtn" data-bs-toggle="modal" data-bs-target="#transactionModal">Создать транзакцию</button>
    <button class="btn-add" id="createRecurringBtn" data-bs-toggle="modal" data-bs-target="#transactionModal">Создать повторяющуюся транзакцию</button>

    <div class="transactions-container">
        <?php if (isset($dataProvider) && $dataProvider->models): ?>
            <?php foreach ($dataProvider->models as $transaction): ?>
                <div class="transaction-card" data-id="<?= $transaction->id ?>">
                    <div class="transaction-info">
                        <p><strong>Дата:</strong> <?= Html::encode($transaction->date) ?></p>
                        <p><strong>Сумма:</strong> <?= Html::encode($transaction->display_amount ?? number_format($transaction->amount, 2)) ?> <?= $currencySymbols[$transaction->display_currency ?? $transaction->currency ?? $userCurrency] ?? '' ?></p>
                        <p><strong>Тип:</strong> <?= Html::encode($transaction->category->type ?? '-') ?></p>
                        <p><strong>Категория:</strong> <?= Html::encode($transaction->category->name ?? '-') ?></p>
                        <p><strong>Описание:</strong> <?= Html::encode($transaction->description ?? '-') ?></p>
                        <?php if ($transaction->recurring_id): ?>
                            <p><strong>Повтор:</strong> <?= Html::encode($transaction->recurringTransaction->displayFrequency()) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="transaction-actions">
                        <button class="editBtn js-update" data-id="<?= $transaction->id ?>">✏️</button>
                        <button class="deleteBtn js-delete" data-id="<?= $transaction->id ?>">🗑️</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет доступных транзакций.</p>
        <?php endif; ?>
    </div>
</div>

<?= $this->render('_modal', ['goals' => $goals]) ?>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [
    'depends' => [JqueryAsset::class],
]);
?>
