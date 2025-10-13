<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $summary */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Транзакции';

$createUrl = Url::to(['transaction/create']);
$updateUrl = Url::to(['transaction/update']);
$deleteUrl = Url::to(['transaction/delete']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Yii::$app->request->csrfToken ?>">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

        body {
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
            background: #f9f7f4;
            margin: 0;
            padding: 0;
            color: #4b453f;
        }
        .sidebar {
            width: 20rem;
            background-color: #b6b6b6;
            color: #8e8e8e;
            padding: 2rem 1rem;
            height: 100vh;
            position: fixed;
        }
        .sidebar h2 {
            font-size: 2.5rem;
            color: #2c2929;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li a {
            color: #1c1b1b;
            text-decoration: none;
            display: block;
            padding: 0.5rem 0;
            font-weight: 500;
        }
        .sidebar ul li a:hover { color: #535353; }

        .content {
            margin-left: 21rem;
            padding: 2rem;
        }
        .content h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .summary-cards {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        .summary-card {
            flex: 1;
            min-width: 14rem;
            background: #fff;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .summary-card h5 {
            font-size: 1rem;
            margin-bottom: 0.3rem;
            color: #6b7280;
        }
        .summary-card p {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .transactions-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .transaction-card {
            background: #fff;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .transaction-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        .transaction-info {
            display: flex;
            flex-direction: column;
        }
        .transaction-info p {
            margin: 0.25rem 0;
            color: #6b7280;
        }
        .transaction-actions button {
            border: none;
            background: none;
            cursor: pointer;
            color: #6b7280;
            font-size: 1rem;
            margin-left: 0.5rem;
        }
        .transaction-actions button:hover { color: #171716; }

        .btn-add {
            background-color: #a3c9c9;
            color: #222020;
            border: none;
            border-radius: 20px;
            padding: 0.6rem 1.2rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        .btn-add:hover {
            background-color: #8da4a4;
            color: #fff;
        }
        @media (max-width: 768px) {
            .sidebar { width: 15rem; }
            .content { margin-left: 16rem; }
        }
        @media (max-width: 576px) {
            .sidebar { width: 100%; position: relative; height: auto; }
            .content { margin-left: 0; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>PastelFinance</h2>
    <ul>
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
            <p><?= number_format($summary['income'] ?? 0, 2) ?></p>
        </div>
        <div class="summary-card">
            <h5>Расход</h5>
            <p><?= number_format($summary['expense'] ?? 0, 2) ?></p>
        </div>
        <div class="summary-card">
            <h5>Баланс</h5>
            <p><?= number_format($summary['balance'] ?? 0, 2) ?></p>
        </div>
    </div>

    <button class="btn-add" id="createTransactionBtn">Создать транзакцию</button>

    <div class="transactions-container">
        <?php foreach ($dataProvider->models as $transaction): ?>
            <div class="transaction-card">
                <div class="transaction-info">
                    <p><strong>Дата:</strong> <?= Html::encode($transaction->date) ?></p>
                    <p><strong>Сумма:</strong> <?= number_format($transaction->amount, 2) ?></p>
                    <p><strong>Тип:</strong> <?= Html::encode($transaction->type) ?></p>
                    <p><strong>Категория:</strong> <?= Html::encode($transaction->category->name ?? '-') ?></p>
                    <p><strong>Описание:</strong> <?= Html::encode($transaction->description) ?></p>
                </div>
                <div class="transaction-actions">
                    <button class="editBtn" data-id="<?= $transaction->id ?>">✏️</button>
                    <button class="deleteBtn" data-id="<?= $transaction->id ?>">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$dataProvider->models): ?>
            <p>Нет доступных транзакций.</p>
        <?php endif; ?>
    </div>
</div>

<?= $this->render('_modal', ['goals' => $goals]); ?>

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
</html>