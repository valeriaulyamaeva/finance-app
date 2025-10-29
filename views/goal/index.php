<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;

/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Цели';
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

<button class="btn-add" id="addGoalBtn" data-bs-toggle="modal" data-bs-target="#goalModal">
    Добавить цель
</button>

<div class="cards-container">
    <?php if ($dataProvider && $dataProvider->models): ?>
        <?php foreach ($dataProvider->models as $goal): ?>
            <div class="card" data-id="<?= $goal->id ?>">
                <div>
                    <h3><?= Html::encode($goal->name) ?></h3>
                    <p>Цель: <?= number_format($goal->display_target_amount ?? $goal->target_amount, 2) ?> <?= Yii::$app->user->identity->currency ?></p>
                    <p>Текущая сумма: <?= number_format($goal->display_current_amount ?? $goal->current_amount, 2) ?> <?= Yii::$app->user->identity->currency ?></p
                    <p>Статус: <?= Html::encode($goal->displayStatus()) ?></p>
                    <p>Срок: <?= Html::encode($goal->deadline) ?></p>
                </div>
                <div class="actions">
                    <button class="editBtn" data-id="<?= $goal->id ?>">✏️</button>
                    <button class="deleteBtn" data-id="<?= $goal->id ?>">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Нет доступных целей.</p>
    <?php endif; ?>
</div>
</div>>

<div class="modal fade" id="goalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="goalForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Создать/обновить цель</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="Goal[id]" id="goalId">
                <div class="mb-3">
                    <label class="form-label">Название</label>
                    <label for="goalName"></label><input type="text" class="form-control" name="Goal[name]" id="goalName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Целевая сумма</label>
                    <label for="goalTarget"></label><input type="number" class="form-control" name="Goal[target_amount]" id="goalTarget" step="0.01" required>
                    <span id="goalCurrencyLabel"></span>
                    <input type="hidden" name="Goal[currency]" id="goalCurrency">
                </div>
                <div class="mb-3">
                    <label class="form-label">Текущая сумма</label>
                    <label for="goalCurrent"></label><input type="number" class="form-control" name="Goal[current_amount]" id="goalCurrent" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Срок</label>
                    <label for="goalDeadline"></label><input type="date" class="form-control" name="Goal[deadline]" id="goalDeadline" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Статус</label>
                    <label for="goalStatus"></label><select class="form-select" name="Goal[status]" id="goalStatus">
                        <option value="active">Активна</option>
                        <option value="completed">Завершена</option>
                        <option value="failed">Не выполнена</option>
                    </select>
                </div>
                <div id="formErrors" class="alert alert-danger mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary saveGoal">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<?php
$urls = [
    'create' => Url::to(['goal/create']),
    'update' => Url::to(['goal/update']),
    'delete' => Url::to(['goal/delete']),
    'view'   => Url::to(['goal/view']),
];
$this->registerJs('const goalUrls = ' . json_encode($urls) . ';', View::POS_HEAD);
$this->registerJs('const userCurrency = "' . Yii::$app->user->identity->currency . '";', View::POS_HEAD);

$this->registerCssFile('@web/css/goal.css');
$this->registerJsFile('@web/js/goal.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [
    'depends' => [JqueryAsset::class],
]);
?>
