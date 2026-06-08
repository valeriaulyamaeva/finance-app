<?php
/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\models\Investment[] $investments */
/** @var array $summary */
/** @var string $userCurrency */

use app\models\Investment;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = 'Инвестиции';

$currencySymbols = ['BYN' => 'Br', 'USD' => '$', 'EUR' => '€', 'RUB' => '₽'];
$cs = $currencySymbols[$userCurrency] ?? $userCurrency;

$this->registerJs('window.investmentConfig = ' . json_encode([
    'urls' => [
        'create' => Url::to(['investment/create']),
        'update' => Url::to(['investment/update']),
        'delete' => Url::to(['investment/delete']),
        'view' => Url::to(['investment/view']),
        'price' => Url::to(['investment/price']),
        'refresh' => Url::to(['investment/refresh']),
    ],
    'userCurrency' => $userCurrency,
]) . ';', View::POS_HEAD);

$cssVer = filemtime(Yii::getAlias('@webroot/css/investment.css'));
$jsVer = filemtime(Yii::getAlias('@webroot/js/investment.js'));
$this->registerCssFile('@web/css/investment.css?v=' . $cssVer);
$this->registerJsFile('@web/js/investment.js?v=' . $jsVer, ['depends' => [JqueryAsset::class]]);
?>

<button class="sidebar-toggle d-lg-none" id="sidebarToggle">
    <i class="fas fa-bars fa-2x"></i>
</button>

<div class="investment-page">
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header d-flex justify-content-between align-items-center d-lg-none">
            <h2>PastelFinance</h2>
            <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times fa-lg"></i></button>
        </div>
        <h2 class="d-none d-lg-block">PastelFinance</h2>
        <ul>
            <li><a href="/analytics">Аналитика</a></li>
            <li><a href="/forecast">Прогнозирование</a></li>
            <li><a href="/transaction">Транзакции</a></li>
            <li><a href="/budget">Бюджеты</a></li>
            <li><a href="/category">Категории</a></li>
            <li><a href="/goal">Цели</a></li>
            <li><a href="/investment" class="active">Инвестиции</a></li>
            <li><a href="/import">Импорт выписки</a></li>
            <li><a href="/settings">Настройки</a></li>
        </ul>
    </div>

    <div class="investment-content" id="mainContent">
        <h1>Инвестиции</h1>

        <div class="summary-cards">
            <div class="summary-card">
                <h5>Вложено</h5>
                <p><?= number_format($summary['invested'], 2) ?> <?= $cs ?></p>
            </div>
            <div class="summary-card">
                <h5>Текущая стоимость</h5>
                <p><?= number_format($summary['current'], 2) ?> <?= $cs ?></p>
            </div>
            <div class="summary-card">
                <h5>Прибыль / убыток</h5>
                <p style="color: <?= $summary['profit'] >= 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
                    <?= $summary['profit'] >= 0 ? '+' : '' ?><?= number_format($summary['profit'], 2) ?> <?= $cs ?>
                </p>
            </div>
            <div class="summary-card">
                <h5>Доходность</h5>
                <p style="color: <?= $summary['profitPercent'] >= 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
                    <?= $summary['profitPercent'] >= 0 ? '+' : '' ?><?= $summary['profitPercent'] ?>%
                </p>
            </div>
        </div>

        <div class="actions mb-4 d-flex gap-2 flex-wrap">
            <button class="btn-add" id="addInvestmentBtn">
                <i class="fas fa-plus-circle me-2"></i>Добавить актив
            </button>
            <button class="btn-refresh" id="refreshPricesBtn" title="Обновить цены с биржи">
                <i class="fas fa-sync-alt me-2"></i>Обновить цены
            </button>
        </div>

        <div class="cards-container">
            <?php if (!empty($investments)): ?>
                <?php foreach ($investments as $inv):
                    $profit = $inv->getProfit();
                    $profitPct = $inv->getProfitPercent();
                    $profitColor = $profit >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
                    ?>
                    <div class="card investment-card" data-id="<?= $inv->id ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="m-0">
                                    <i class="fas <?= $inv->getTypeIcon() ?> me-2 text-muted"></i>
                                    <?= Html::encode($inv->name) ?>
                                </h3>
                                <span class="investment-type-badge"><?= Html::encode($inv->getTypeLabel()) ?></span>
                            </div>

                            <div class="investment-values">
                                <div class="investment-value-row">
                                    <span class="text-muted">Текущая</span>
                                    <span class="investment-current"><?= number_format($inv->current_value, 2) ?> <?= Html::encode($inv->currency) ?></span>
                                </div>
                                <div class="investment-value-row">
                                    <span class="text-muted">Вложено</span>
                                    <span><?= number_format($inv->invested_amount, 2) ?> <?= Html::encode($inv->currency) ?></span>
                                </div>
                                <div class="investment-value-row">
                                    <span class="text-muted">Прибыль</span>
                                    <span style="color: <?= $profitColor ?>; font-weight: 600;">
                                        <?= $profit >= 0 ? '+' : '' ?><?= number_format($profit, 2) ?>
                                        (<?= $profitPct >= 0 ? '+' : '' ?><?= $profitPct ?>%)
                                    </span>
                                </div>
                            </div>

                            <?php if ($inv->purchase_date): ?>
                                <p class="text-muted small mt-2 mb-0">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    куплено <?= Yii::$app->formatter->asDate($inv->purchase_date, 'long') ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($inv->note): ?>
                                <p class="text-muted small mt-1 mb-0"><?= Html::encode($inv->note) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="card-actions">
                            <button class="editBtn" data-id="<?= $inv->id ?>" title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="deleteBtn" data-id="<?= $inv->id ?>" title="Удалить">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-100 text-center py-5">
                    <p class="text-muted fs-4">У вас пока нет активов</p>
                    <p class="text-muted">Добавьте акции, криптовалюту, вклады или другие инвестиции, чтобы учитывать их в общем капитале.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="investmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 overflow-hidden">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-coins me-2"></i>
                    <span id="investmentModalTitle">Новый актив</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="investmentForm" class="modal-body p-4">
                <input type="hidden" id="investment-id">

                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="app-field mb-3">
                            <label for="inv-name" class="app-label">Название</label>
                            <input type="text" id="inv-name" name="Investment[name]" class="form-control" placeholder="Например, Tesla или Bitcoin" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="app-field mb-3">
                            <label for="inv-type" class="app-label">Тип</label>
                            <select id="inv-type" name="Investment[type]" class="form-select" required>
                                <?php foreach (Investment::getTypes() as $value => $label): ?>
                                    <option value="<?= $value ?>"><?= Html::encode($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Live-price section (crypto / stocks) -->
                <div class="market-price-box" id="marketPriceBox">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <div class="app-field">
                                <label for="inv-ticker" class="app-label">
                                    Тикер <span class="text-muted small">(BTC, ETH, AAPL…)</span>
                                </label>
                                <input type="text" id="inv-ticker" name="Investment[ticker]" class="form-control" placeholder="BTC" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="app-field">
                                <label for="inv-quantity" class="app-label">Количество</label>
                                <input type="number" step="0.00000001" id="inv-quantity" name="Investment[quantity]" class="form-control" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn-fetch-price" id="fetchPriceBtn">
                                <i class="fas fa-download me-1"></i> Цена
                            </button>
                        </div>
                    </div>
                    <div class="market-price-result" id="marketPriceResult"></div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="app-field mb-3">
                            <label for="inv-invested" class="app-label">Вложено</label>
                            <input type="number" step="0.01" id="inv-invested" name="Investment[invested_amount]" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="app-field mb-3">
                            <label for="inv-current" class="app-label">Текущая стоимость</label>
                            <input type="number" step="0.01" id="inv-current" name="Investment[current_value]" class="form-control" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="app-field mb-3">
                            <label for="inv-currency" class="app-label">Валюта</label>
                            <select id="inv-currency" name="Investment[currency]" class="form-select">
                                <?php foreach (['BYN', 'USD', 'EUR', 'RUB'] as $c): ?>
                                    <option value="<?= $c ?>" <?= $c === $userCurrency ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="app-field mb-3">
                    <label for="inv-date" class="app-label">Дата покупки <span class="text-muted small">(необязательно)</span></label>
                    <input type="date" id="inv-date" name="Investment[purchase_date]" class="form-control">
                </div>

                <div class="app-field mb-3">
                    <label for="inv-note" class="app-label">Заметка <span class="text-muted small">(необязательно)</span></label>
                    <textarea id="inv-note" name="Investment[note]" class="form-control" rows="2" placeholder="Например: брокер, кол-во акций"></textarea>
                </div>

                <div id="formErrors" class="alert alert-danger d-none"></div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="investmentSaveBtn">
                    <i class="fas fa-save me-1"></i> Сохранить
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>
