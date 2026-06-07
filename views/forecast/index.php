<?php
/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var array $monthEnd */
/** @var array $categories */
/** @var array $goals */
/** @var array $timeline */
/** @var string $userCurrency */

use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = 'Прогнозирование';

$currencySymbols = ['BYN' => 'Br', 'USD' => '$', 'EUR' => '€', 'RUB' => '₽'];
$cs = $currencySymbols[$userCurrency] ?? $userCurrency;

$this->registerJs('window.forecastConfig = ' . json_encode([
    'timeline' => $timeline,
    'currencySymbol' => $cs,
]) . ';', View::POS_HEAD);

$cssVer = filemtime(Yii::getAlias('@webroot/css/forecast.css'));
$jsVer = filemtime(Yii::getAlias('@webroot/js/forecast.js'));
$this->registerCssFile('@web/css/forecast.css?v=' . $cssVer);
$this->registerJsFile('@web/js/forecast.js?v=' . $jsVer, ['depends' => [JqueryAsset::class]]);
?>

<button class="sidebar-toggle d-lg-none" id="sidebarToggle">
    <i class="fas fa-bars fa-2x"></i>
</button>

<div class="forecast-page">
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header d-flex justify-content-between align-items-center d-lg-none">
            <h2>PastelFinance</h2>
            <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times fa-lg"></i></button>
        </div>
        <h2 class="d-none d-lg-block">PastelFinance</h2>
        <ul>
            <li><a href="/analytics">Аналитика</a></li>
            <li><a href="/forecast" class="active">Прогнозирование</a></li>
            <li><a href="/transaction">Транзакции</a></li>
            <li><a href="/budget">Бюджеты</a></li>
            <li><a href="/category">Категории</a></li>
            <li><a href="/goal">Цели</a></li>
            <li><a href="/import">Импорт выписки</a></li>
            <li><a href="/settings">Настройки</a></li>
        </ul>
    </div>

    <div class="forecast-content" id="mainContent">
        <h1>Прогнозирование</h1>

        <!-- Month-end forecast summary -->
        <div class="summary-cards">
            <div class="summary-card">
                <h5>Баланс сейчас</h5>
                <p><?= number_format($monthEnd['currentBalance'], 2) ?> <?= $cs ?></p>
            </div>
            <div class="summary-card">
                <h5>Прогноз на конец месяца</h5>
                <p style="color: <?= $monthEnd['projectedBalance'] >= $monthEnd['currentBalance'] ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
                    <?= number_format($monthEnd['projectedBalance'], 2) ?> <?= $cs ?>
                </p>
            </div>
            <div class="summary-card">
                <h5>Ожидаемые расходы (<?= $monthEnd['daysLeft'] ?> дн)</h5>
                <p style="color: var(--color-danger);"><?= number_format($monthEnd['projectedExpense'], 2) ?> <?= $cs ?></p>
            </div>
            <div class="summary-card">
                <h5>Средний расход в день</h5>
                <p><?= number_format($monthEnd['avgDailyExpense'], 2) ?> <?= $cs ?></p>
            </div>
        </div>

        <!-- Balance timeline chart -->
        <div class="chart-card forecast-chart-card">
            <h4>Баланс: история и прогнозирование</h4>
            <canvas id="balanceTimelineChart"></canvas>
        </div>

        <div class="forecast-columns">
            <!-- Category forecasts -->
            <div class="forecast-block">
                <h4><i class="fas fa-chart-line me-2"></i>Расходы по категориям в этом месяце</h4>
                <?php if (empty($categories)): ?>
                    <p class="text-muted">Недостаточно истории для прогнозирования (нужно минимум 3 месяца данных)</p>
                <?php else: ?>
                    <div class="forecast-list">
                        <?php foreach (array_slice($categories, 0, 10) as $cat): ?>
                            <div class="forecast-item forecast-item--<?= $cat['status'] ?>">
                                <div class="forecast-item-head">
                                    <span class="forecast-item-name"><?= Html::encode($cat['category']) ?></span>
                                    <?php if ($cat['status'] === 'over'): ?>
                                        <span class="forecast-badge forecast-badge--over">↑ выше обычного</span>
                                    <?php elseif ($cat['status'] === 'under'): ?>
                                        <span class="forecast-badge forecast-badge--under">↓ ниже обычного</span>
                                    <?php endif; ?>
                                </div>
                                <div class="forecast-item-bar">
                                    <?php
                                    $pct = $cat['avgMonthly'] > 0 ? min(100, ($cat['projected'] / $cat['avgMonthly']) * 100) : 0;
                                    ?>
                                    <div class="forecast-item-fill" style="width: <?= round($pct) ?>%;"></div>
                                </div>
                                <div class="forecast-item-meta">
                                    <span>Потрачено: <?= number_format($cat['currentSpent'], 2) ?> <?= $cs ?></span>
                                    <span>Прогноз: <b><?= number_format($cat['projected'], 2) ?></b> / обычно <?= number_format($cat['avgMonthly'], 2) ?> <?= $cs ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Goal forecasts -->
            <div class="forecast-block">
                <h4><i class="fas fa-bullseye me-2"></i>Достижение целей</h4>
                <?php if (empty($goals)): ?>
                    <p class="text-muted">Нет активных целей</p>
                <?php else: ?>
                    <div class="forecast-list">
                        <?php foreach ($goals as $g): ?>
                            <div class="forecast-item">
                                <div class="forecast-item-head">
                                    <span class="forecast-item-name"><?= Html::encode($g['name']) ?></span>
                                    <?php if ($g['onTrack'] === true): ?>
                                        <span class="forecast-badge forecast-badge--ontrack">✓ успеваете</span>
                                    <?php elseif ($g['onTrack'] === false && $g['etaDate']): ?>
                                        <span class="forecast-badge forecast-badge--late">⚠ не успеваете</span>
                                    <?php else: ?>
                                        <span class="forecast-badge forecast-badge--stale">нет пополнений</span>
                                    <?php endif; ?>
                                </div>
                                <div class="forecast-item-bar">
                                    <div class="forecast-item-fill forecast-item-fill--goal" style="width: <?= round($g['progress']) ?>%;"></div>
                                </div>
                                <div class="forecast-item-meta">
                                    <span><?= number_format($g['current'], 2) ?> / <?= number_format($g['target'], 2) ?> <?= Html::encode($g['currency']) ?></span>
                                    <span>
                                        <?php if ($g['etaDate']): ?>
                                            При текущем темпе: <b><?= Yii::$app->formatter->asDate($g['etaDate'], 'long') ?></b>
                                            (дедлайн <?= Yii::$app->formatter->asDate($g['deadline'], 'short') ?>)
                                        <?php else: ?>
                                            Дедлайн: <?= Yii::$app->formatter->asDate($g['deadline'], 'long') ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>
