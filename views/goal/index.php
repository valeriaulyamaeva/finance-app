<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;

/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Цели';
$userCurrency = Yii::$app->user->identity->currency ?? 'BYN';
$currencySymbols = ['BYN' => 'Br', 'EUR' => '€', 'USD' => '$', 'RUB' => '₽'];
$currencySymbol = $currencySymbols[$userCurrency] ?? $userCurrency;

$urls = [
    'create' => Url::to(['goal/create']),
    'update' => Url::to(['goal/update']),
    'delete' => Url::to(['goal/delete']),
    'view'   => Url::to(['goal/view']),
];
$this->registerJs('const goalUrls = ' . json_encode($urls) . ';', View::POS_HEAD);
$this->registerJs('const userCurrency = "' . $userCurrency . '";', View::POS_HEAD);
$this->registerJs('const currencySymbol = "' . $currencySymbol . '";', View::POS_HEAD);

$this->registerCssFile('@web/css/goal.css?v=2');
$this->registerJsFile('@web/js/goal.js?v=2', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>

    <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
        <i class="fas fa-bars fa-2x"></i>
    </button>

    <div class="goal-page">
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
                <li><a href="/budget">Бюджеты</a></li>
                <li><a href="/category">Категории</a></li>
                <li><a href="/goal" class="active">Цели</a></li>
                <li><a href="/settings">Настройки</a></li>
            </ul>
        </div>

        <div class="goal-content" id="mainContent">
            <h1>Цели</h1>

            <div class="actions mb-4">
                <button class="btn-add" id="addGoalBtn" data-bs-toggle="modal" data-bs-target="#goalModal">
                    Добавить цель
                </button>
            </div>

            <div class="cards-container">
                <?php if ($dataProvider && $dataProvider->getModels()): ?>
                    <?php foreach ($dataProvider->getModels() as $goal):
                        $target = $goal->display_target_amount ?? $goal->target_amount;
                        $current = $goal->display_current_amount ?? $goal->current_amount;
                        $percent = $target > 0 ? min(100, round(($current / $target) * 100, 1)) : 0;
                        $progressColor = $percent >= 100 ? '#16a34a' : ($percent >= 75 ? '#facc15' : '#8da4a4');
                        $statusColor = $goal->status === 'completed' ? '#16a34a' : ($goal->status === 'failed' ? '#dc2626' : '#8da4a4');
                        ?>
                        <div class="card" data-id="<?= $goal->id ?>">
                            <div class="card-body">
                                <h3><?= Html::encode($goal->name) ?></h3>

                                <p><strong>Срок:</strong> <?= Yii::$app->formatter->asDate($goal->deadline, 'd MMMM yyyy') ?></p>
                                <p>
                                    <strong>Статус:</strong>
                                    <span style="color: <?= $statusColor ?>; font-weight: 600;">
                                    <?= Html::encode($goal->displayStatus()) ?>
                                </span>
                                </p>

                                <div class="goal-progress-bar">
                                    <div class="goal-progress-fill" style="width: <?= $percent ?>%; background: <?= $progressColor ?>;"></div>
                                </div>
                                <p class="goal-progress-text">
                                    <strong><?= $percent ?>%</strong> накоплено — <?= number_format($current, 2) ?> из <?= number_format($target, 2) ?> <?= $currencySymbol ?>
                                </p>
                            </div>

                            <div class="card-actions">
                                <button class="editBtn" data-id="<?= $goal->id ?>">✏️</button>
                                <button class="deleteBtn" data-id="<?= $goal->id ?>">🗑️</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted py-5 fs-4">У вас пока нет целей. Создайте первую!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="goalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header bg-gradient-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="goalModalTitle">Создать цель</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="goalForm" class="modal-body p-4">
                    <input type="hidden" name="Goal[id]" id="goalId">
                    <input type="hidden" name="Goal[currency]" id="goalCurrency" value="<?= $userCurrency ?>">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-3" name="Goal[name]" id="goalName"  required>
                        <label for="goalName">Название цели</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" step="0.01" class="form-control rounded-3" name="Goal[target_amount]" id="goalTarget"  required>
                        <label for="goalTarget">Целевая сумма (<?= $currencySymbol ?>)</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" step="0.01" class="form-control rounded-3" name="Goal[current_amount]" id="goalCurrent" >
                        <label for="goalCurrent">Текущая сумма (по умолчанию 0)</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="date" class="form-control rounded-3" name="Goal[deadline]" id="goalDeadline" required>
                        <label for="goalDeadline">Срок достижения</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Статус</label>
                        <select class="form-select rounded-3" name="Goal[status]" id="goalStatus">
                            <option value="active">Активна</option>
                            <option value="completed">Завершена</option>
                            <option value="failed">Не выполнена</option>
                        </select>
                    </div>

                    <div id="formErrors" class="alert alert-danger mt-3 d-none"></div>

                    <div class="modal-footer bg-light border-0 px-0 pt-3">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5">
                            <i class="fas fa-save me-2"></i> Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js');
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>