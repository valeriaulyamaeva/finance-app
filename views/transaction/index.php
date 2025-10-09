<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $summary */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Транзакции';

/** URL'ы для AJAX */
$createUrl = Url::to(['transaction/create']);
$updateUrl = Url::to(['transaction/update']);
$deleteUrl = Url::to(['transaction/delete']);
?>

    <div class="transaction-index container mt-4">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card p-3">
                    <div>Доход</div>
                    <div class="h4"><?= number_format($summary['income'] ?? 0, 2) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <div>Расход</div>
                    <div class="h4"><?= number_format($summary['expense'] ?? 0, 2) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <div>Баланс</div>
                    <div class="h4"><?= number_format($summary['balance'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button class="btn btn-success" id="createTransactionBtn">Создать транзакцию</button>
        </div>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'date',
                [
                    'attribute' => 'amount',
                    'format' => 'decimal',
                ],
                'type',
                [
                    'attribute' => 'category_id',
                    'value' => function ($model) {
                        return $model->category->name ?? '-';
                    }
                ],
                'description:ntext',
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

        <?= $this->render('_modal', ['goals' => $goals]); ?>
    </div>

<?php
$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['position' => \yii\web\View::POS_END]);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$js = <<<JS
$('#createTransactionBtn').on('click', function() {
    $('#transactionForm')[0].reset();
    new bootstrap.Modal(document.getElementById('transactionModal')).show();
    $('#transactionModal').data('action', 'create');
});

$('.saveTransaction').on('click', function() {
    var action = $('#transactionModal').data('action') || 'create';
    var id = $('#transactionModal').data('id') || '';
    var url = action === 'create' ? '$createUrl' : '$updateUrl?id=' + id;
    var data = $('#transactionForm').serialize();
    console.log('Sending request to:', url, 'Data:', data);

    $.post(url, data)
        .done(function(res) {
            console.log('Response:', res);
            if (res.success) {
                location.reload();
            } else {
                alert(res.message || 'Ошибка');
            }
        })
        .fail(function(xhr) {
            console.error('Error:', xhr.responseText);
            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.responseText;
            alert(msg || 'Сервер вернул ошибку');
        });
});

$(document).on('click', '.js-update', function() {
    var id = $(this).data('id');
    console.log('Fetching transaction:', id);
    $.get('transaction/view', {id: id})
        .done(function(res) {
            console.log('View response:', res);
            if (res.success && res.transaction) {
                var t = res.transaction;
                $('#transactionForm input[name="Transaction[amount]"]').val(t.amount);
                $('#transactionForm input[name="Transaction[date]"]').val(t.date);
                $('#transactionForm select[name="Transaction[category_id]"]').val(t.category_id);
                $('#transactionForm textarea[name="Transaction[description]"]').val(t.description);
                $('#transactionModal').data('action', 'update').data('id', id);
                new bootstrap.Modal(document.getElementById('transactionModal')).show();
            } else {
                alert(res.message || 'Не удалось получить транзакцию');
            }
        })
        .fail(function(xhr) {
            console.error('View error:', xhr.responseText);
            alert('Ошибка получения данных');
        });
});

$(document).on('click', '.js-delete', function() {
    if (!confirm('Удалить транзакцию?')) return;
    var id = $(this).data('id');
    console.log('Deleting transaction:', id);
    $.post('$deleteUrl?id=' + id)
        .done(function(res) {
            console.log('Delete response:', res);
            if (res.success) location.reload();
            else alert(res.message || 'Ошибка удаления');
        })
        .fail(function(xhr) {
            console.error('Delete error:', xhr.responseText);
            alert('Сервер вернул ошибку');
        });
});
JS;
$this->registerJs($js);
?>
