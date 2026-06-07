<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $summary */
/** @var array $goals */
/** @var app\models\Category[] $categories */
/** @var string $startDate */
/** @var string $endDate */
/** @var int|null $categoryId */

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = 'Транзакции';
$this->registerCssFile('@web/css/notifications.css');
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
AppAsset::register($this);

$currencySymbols = ['BYN'=>'Br','USD'=>'$','EUR'=>'€', 'RUB' => '₽'];
$userCurrency = Yii::$app->user->identity->currency ?? 'BYN';

$urls = [
    'create' => Url::to(['transaction/create']),
    'update' => Url::to(['transaction/update']),
    'delete' => Url::to(['transaction/delete']),
    'view' => Url::to(['transaction/view']),
    'createRecurring' => Url::to(['recurring-transaction/create']),
    'recurringList' => Url::to(['recurring-transaction/list']),
    'recurringDelete' => Url::to(['recurring-transaction/delete']),
    'recurringCreate' => Url::to(['recurring-transaction/create']),
    'recurringUpdate' => Url::to(['recurring-transaction/update']),
];

$this->registerJs('window.transactionConfig = ' . json_encode([
        'urls' => $urls,
        'currencySymbols' => $currencySymbols,
        'userCurrency' => $userCurrency,
    ]) . ';', View::POS_HEAD);

$assetVersion = filemtime(Yii::getAlias('@webroot/js/transaction.js'));
$this->registerCssFile('@web/css/transaction.css?v=' . filemtime(Yii::getAlias('@webroot/css/transaction.css')));
$this->registerJsFile('@web/js/transaction.js?v=' . $assetVersion, ['depends' => [JqueryAsset::class]]);
?>

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
        <li><a href="/analytics">Аналитика</a></li>
                <li><a href="/forecast">Прогнозирование</a></li>
        <li><a href="/transaction" class="active">Транзакции</a></li>
        <li><a href="/budget">Бюджеты</a></li>
        <li><a href="/category">Категории</a></li>
        <li><a href="/goal">Цели</a></li>
        <li><a href="/import">Импорт выписки</a></li>
        <li><a href="/settings">Настройки</a></li>
    </ul>
</div>

    <div class="transaction-content" id="mainContent">
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

        <div class="actions mb-4">
            <button class="btn-add" id="createTransactionBtn" data-bs-toggle="modal" data-bs-target="#transactionModal">
                Создать транзакцию
            </button>
            <button class="btn-add" id="viewRecurringBtn" data-bs-toggle="modal" data-bs-target="#recurringModal">
                Регулярные платежи
            </button>
        </div>

        <div class="filter-bar">
            <div class="filter-group">
                <label for="filterStart">С</label>
                <input type="date" id="filterStart" value="<?= Html::encode($startDate) ?>">
            </div>
            <div class="filter-group">
                <label for="filterEnd">По</label>
                <input type="date" id="filterEnd" value="<?= Html::encode($endDate) ?>">
            </div>
            <div class="filter-group">
                <label for="filterCategory">Категория</label>
                <select id="filterCategory">
                    <option value="">Все</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= $categoryId == $cat->id ? 'selected' : '' ?>>
                            <?= Html::encode($cat->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn-add" id="applyFilter">Применить</button>
            <button class="btn-reset-filter" id="resetFilter">Сбросить</button>
        </div>

        <div class="cards-container">
            <?php if ($dataProvider->models): ?>
                <?php foreach ($dataProvider->models as $transaction):
                    $isIncome = $transaction->type === 'income';
                    $amountColor = $isIncome ? '#16a34a' : '#dc2626';
                    $sign = $isIncome ? '+' : '−';
                    $currency = $transaction->display_currency ?? $transaction->currency ?? $userCurrency;
                    $symbol = $currencySymbols[$currency] ?? $currency;
                    ?>
                    <div class="card<?= $transaction->category_id ? '' : ' card--no-category' ?>" data-id="<?= $transaction->id ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="m-0"><?= Html::encode($transaction->category->name ?? 'Без категории') ?></h3>
                                <span class="transaction-amount" style="color: <?= $amountColor ?>;">
                                    <?= $sign ?><?= number_format($transaction->display_amount ?? $transaction->amount, 2) ?> <?= $symbol ?>
                                </span>
                            </div>

                            <p class="text-muted small mb-2">
                                <i class="far fa-calendar-alt me-1"></i>
                                <?= Yii::$app->formatter->asDate($transaction->date, 'long') ?>
                            </p>

                            <?php if (!empty($transaction->description)): ?>
                                <p class="transaction-description"><?= Html::encode($transaction->description) ?></p>
                            <?php endif; ?>

                            <?php if ($transaction->recurring_id): ?>
                                <p class="text-muted small mt-2">
                                    <i class="fas fa-redo me-1"></i>
                                    <?= Html::encode($transaction->recurringTransaction->displayFrequency()) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="card-actions">
                            <button class="editBtn js-update" data-id="<?= $transaction->id ?>" title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="deleteBtn js-delete" data-id="<?= $transaction->id ?>" title="Удалить">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-100 text-center py-5">
                    <p class="text-muted fs-4">Нет транзакций за выбранный период</p>
                </div>
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
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>
