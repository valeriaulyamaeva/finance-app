<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Цели';

/** URL'ы для AJAX */
$createUrl = Url::to(['goal/create']);
$updateUrl = Url::to(['goal/update']);
$deleteUrl = Url::to(['goal/delete']);
?>

<div class="goal-index container mt-4">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="mb-3">
        <button class="btn btn-success" id="createGoalBtn">Создать цель</button>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            'target_amount',
            'current_amount',
            [
                'attribute' => 'status',
                'value' => function($model) { return $model->displayStatus(); },
            ],
            'deadline',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function($url, $model) {
                        return Html::button('Редактировать', [
                            'class' => 'btn btn-primary btn-sm updateGoalBtn',
                            'data-id' => $model->id
                        ]);
                    },
                    'delete' => function($url, $model) use ($deleteUrl) {
                        return Html::a('Удалить', $deleteUrl . '?id=' . $model->id, [
                            'class' => 'btn btn-danger btn-sm',
                            'data-method' => 'post',
                            'data-confirm' => 'Вы уверены, что хотите удалить эту цель?'
                        ]);
                    }
                ],
            ],
        ],
    ]) ?>
</div>

<div class="modal fade" id="goalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="goalForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="goalModalTitle">Создать цель</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="goalId" name="Goal[id]" value="">
                    <div class="mb-3">
                        <label for="goalName" class="form-label">Название</label>
                        <input type="text" class="form-control" id="goalName" name="Goal[name]" required>
                    </div>
                    <div class="mb-3">
                        <label for="goalTarget" class="form-label">Целевая сумма</label>
                        <input type="number" class="form-control" id="goalTarget" name="Goal[target_amount]" required step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="goalCurrent" class="form-label">Текущая сумма</label>
                        <input type="number" class="form-control" id="goalCurrent" name="Goal[current_amount]" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="goalDeadline" class="form-label">Срок</label>
                        <input type="date" class="form-control" id="goalDeadline" name="Goal[deadline]" required>
                    </div>
                    <div class="mb-3">
                        <label for="goalStatus" class="form-label">Статус</label>
                        <select class="form-select" id="goalStatus" name="Goal[status]">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button type="submit" class="btn btn-primary" id="saveGoalBtn">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$script = <<<JS
const goalModal = new bootstrap.Modal(document.getElementById('goalModal'));

$('#createGoalBtn').on('click', function() {
    $('#goalForm')[0].reset();
    $('#goalId').val('');
    $('#goalModalTitle').text('Создать цель');
    goalModal.show();
});

$('.updateGoalBtn').on('click', function() {
    const id = $(this).data('id');
    $.get('{$updateUrl}', {id: id}, function(data) {
        $('#goalModalTitle').text('Редактировать цель');
        $('#goalId').val(data.id);
        $('#goalName').val(data.name);
        $('#goalTarget').val(data.target_amount);
        $('#goalCurrent').val(data.current_amount);
        $('#goalDeadline').val(data.deadline);
        $('#goalStatus').val(data.status);
        goalModal.show();
    }, 'json');
});

$('#goalForm').on('submit', function(e) {
    e.preventDefault();
    const id = $('#goalId').val();
    const url = id ? '{$updateUrl}?id=' + id : '{$createUrl}';
    $.post(url, $(this).serialize(), function() {
        location.reload();
    });
});
JS;
$this->registerJs($script);
?>
