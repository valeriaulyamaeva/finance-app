<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $summary */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Бюджеты';

/** URL'ы для AJAX */
$createUrl = Url::to(['budget/create']);
$updateUrl = Url::to(['budget/update']);
$deleteUrl = Url::to(['budget/delete']);
?>

<div class="budget-index container mt-4">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card p-3">
                <div>Доход</div>
                <div class="h4"><?= number_format($summary['total_budget'] ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div>Расход</div>
                <div class="h4"><?= number_format($summary['total_spent'] ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div>Баланс</div>
                <div class="h4"><?= number_format($summary['remaining'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>


    <div class="mb-3">
        <button class="btn btn-success" id="createBudgetBtn">Создать бюджет</button>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            [
                'attribute' => 'amount',
                'format' => 'decimal',
            ],
            [
                'attribute' => 'period',
                'value' => function($model) {
                    return $model->displayPeriod();
                }
            ],
            [
                'attribute' => 'category_id',
                'value' => function ($model) {
                    return $model->category->name ?? '-';
                }
            ],
            'start_date',
            'end_date',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::button('Изм', ['class' => 'btn btn-sm btn-warning js-update', 'data-id' => $model->id]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::button('Удл', ['class' => 'btn btn-sm btn-danger js-delete', 'data-id' => $model->id]);
                    },
                ],
            ],
        ],
    ]); ?>

    <?= $this->render('_modal'); ?>
</div>

<?php
$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['position' => \yii\web\View::POS_END]);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$js = <<<JS
$('#createBudgetBtn').on('click', function() {
    $('#budgetForm')[0].reset();
    new bootstrap.Modal(document.getElementById('_modal')).show();
    $('#_modal').data('action', 'create');
});

$('.saveBudget').on('click', function() {
    var action = $('#_modal').data('action') || 'create';
    var id = $('#_modal').data('id') || '';
    var url = action === 'create' ? '{$createUrl}' : '{$updateUrl}?id=' + id;
    var data = $('#budgetForm').serialize();

    $.post(url, data)
        .done(function(res) {
            if (res.success) {
                location.reload();
            } else {
                $('#formErrors').text(res.message || 'Ошибка');
                alert(res.message || 'Ошибка');
            }
        })
        .fail(function(xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.responseText || 'Сервер вернул ошибку');
            $('#formErrors').text(msg);
            alert(msg);
        });
});

$(document).on('click', '.js-update', function() {
    var id = $(this).data('id');
    $.get('budget/view', {id: id})
        .done(function(res) {
            if (res.success && res.budget) {
                var b = res.budget;
                $('#budgetForm input[name="Budget[name]"]').val(b.name);
                $('#budgetForm input[name="Budget[amount]"]').val(b.amount);
                $('#budgetForm select[name="Budget[period]"]').val(b.period);
                $('#budgetForm select[name="Budget[category_id]"]').val(b.category_id);
                $('#budgetForm input[name="Budget[start_date]"]').val(b.start_date);
                $('#budgetForm input[name="Budget[end_date]"]').val(b.end_date);
                $('#_modal').data('action', 'update').data('id', id);
                new bootstrap.Modal(document.getElementById('_modal')).show();
            } else {
                alert(res.message || 'Не удалось получить бюджет');
            }
        })
        .fail(function() {
            alert('Ошибка получения данных');
        });
});

$(document).on('click', '.js-delete', function() {
    if (!confirm('Удалить бюджет?')) return;
    var id = $(this).data('id');
    $.post('$deleteUrl?id=' + id)
        .done(function(res) {
            if (res.success) location.reload();
            else alert(res.message || 'Ошибка удаления');
        })
        .fail(function() {
            alert('Сервер вернул ошибку');
        });
});

JS;
$this->registerJs($js);
?>
