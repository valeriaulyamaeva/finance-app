<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;
use yii\web\YiiAsset;

/** @var yii\web\View $this */
/** @var app\models\Goal[] $goals */
/** @var app\models\User $user */
/** @var app\services\CurrencyService $currencyService */

$this->title = 'Финансовые цели';

$userCurrency = $user->currency ?? 'BYN';
$currencySymbols = ['BYN' => 'Br', 'EUR' => '€', 'USD' => '$', 'RUB' => '₽'];
$currencySymbol = $currencySymbols[$userCurrency] ?? $userCurrency;

$jsVars = [
    'createUrl' => Url::to(['goal/create']),
    'updateUrl' => Url::to(['goal/update']),
    'deleteUrl' => Url::to(['goal/delete']),
    'viewUrl'   => Url::to(['goal/view']),
    'userCurrency' => $userCurrency,
    'currencySymbol' => $currencySymbol,
];

$this->registerJs('const goalConfig = ' . json_encode($jsVars) . ';', View::POS_HEAD);

$this->registerCssFile('@web/css/goal.css');
$this->registerJsFile('@web/js/goal.js', [
    'depends' => [
        YiiAsset::class,
        JqueryAsset::class
    ]
]);
?>

    <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
        <i class="fas fa-bars fa-2x"></i>
    </button>

    <div class="goal-page">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-center d-lg-none">
                <h2>PastelFinance</h2>
                <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times fa-lg"></i></button>
            </div>
            <h2 class="d-none d-lg-block">PastelFinance</h2>
            <ul>
                <li><a href="/analytics">Аналитика</a></li>
                <li><a href="/transaction">Транзакции</a></li>
                <li><a href="/budget">Бюджеты</a></li>
                <li><a href="/category">Категории</a></li>
                <li><a href="/goal" class="active">Цели</a></li>
                <li><a href="/import">Импорт выписки</a></li>
                <li><a href="/settings">Настройки</a></li>
            </ul>
        </div>

        <div class="goal-content" id="mainContent">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h1>Цели</h1>
                <button class="btn-add" id="addGoalBtn">
                    <i class="fas fa-plus-circle me-2"></i>Добавить цель
                </button>
            </div>

            <div class="cards-container">
                <?php if (!empty($goals)): ?>
                    <?php foreach ($goals as $goal):
                        $percent = $goal->getProgress();
                        $progressColor = match(true) {
                            $percent >= 100 => '#16a34a',
                            $percent >= 70 => '#facc15',
                            default => '#8da4a4'
                        };
                        ?>
                        <div class="card" data-id="<?= $goal->id ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h3 class="m-0"><?= Html::encode($goal->name) ?></h3>
                                    <span class="badge bg-<?= $goal->getStatusColor() ?> rounded-pill">
                                    <?= $goal->getStatusLabel() ?>
                                </span>
                                </div>

                                <p class="text-muted small mb-3">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    до <?= Yii::$app->formatter->asDate($goal->deadline, 'long') ?>
                                </p>

                                <div class="goal-progress-section">
                                    <div class="goal-progress-bar">
                                        <div class="goal-progress-fill" style="width: <?= $percent ?>%; background-color: <?= $progressColor ?>;"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 small">
                                        <span class="fw-bold"><?= $percent ?>%</span>
                                        <span>
                                            <?= Yii::$app->formatter->asDecimal($goal->current_amount, 2) ?>
                                            / <?= Yii::$app->formatter->asDecimal($goal->target_amount, 2) ?> <?= Html::encode($goal->currency) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="card-actions">
                                <button class="editBtn" data-id="<?= $goal->id ?>" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="deleteBtn" data-id="<?= $goal->id ?>" title="Удалить">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="w-100 text-center py-5">
                        <p class="text-muted fs-4">У вас пока нет активных целей</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="goalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="goalModalTitle">Создать цель</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="goalForm" class="modal-body p-4">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="Goal[name]" id="goalName" placeholder="Название" required>
                        <label for="goalName">Название цели</label>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-8">
                            <div class="form-floating mb-3">
                                <input type="number" step="0.01" class="form-control" name="Goal[target_amount]" id="goalTarget" placeholder="Сумма" required>
                                <label for="goalTarget">Целевая сумма</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="Goal[currency]" id="goalCurrency">
                                    <option value="BYN">BYN</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="RUB">RUB</option>
                                </select>
                                <label for="goalCurrency">Валюта</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" name="Goal[deadline]" id="goalDeadline" required>
                        <label for="goalDeadline">К какому числу накопить?</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" step="0.01" class="form-control" name="Goal[current_amount]" id="goalCurrent" placeholder="Уже есть">
                        <label for="goalCurrent">Уже накоплено (необязательно)</label>
                    </div>

                    <div id="formErrors" class="alert alert-danger d-none"></div>

                    <div class="modal-footer border-0 px-0 pb-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>