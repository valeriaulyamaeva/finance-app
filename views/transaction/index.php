<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $summary */
/** @var array $goals */

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;

$this->title = 'Транзакции';

AppAsset::register($this);
$currencySymbols = ['BYN'=>'Br','USD'=>'$','EUR'=>'€', 'RUB' => '₽'];
$userCurrency = Yii::$app->user->identity->currency ?? 'BYN';

$createUrl = Url::to(['transaction/create']);
$updateUrl = Url::to(['transaction/update']);
$deleteUrl = Url::to(['transaction/delete']);
$viewUrl = Url::to(['transaction/view']);
$createRecurringUrl = Url::to(['recurring-transaction/create']);
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
            padding: 2rem;
            margin-left: 10rem;
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
            margin-right: 1rem;
        }
        .btn-add:hover {
            background-color: #8da4a4;
            color: #fff;
        }
        .modal-dialog {
            max-width: 450px;
            margin: 2rem auto;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .modal-header {
            background-color: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 1.25rem 1.5rem;
        }
        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #4b453f;
        }
        .btn-close {
            filter: invert(0.5);
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }
        .btn-close:hover {
            opacity: 1;
        }
        .modal-body {
            background-color: #fff;
            padding: 1.5rem;
        }
        .form-label {
            font-weight: 500;
            color: #4b453f;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.6rem 0.75rem;
            border: 1px solid #d1d5db;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #a3c9c9;
            outline: none;
            box-shadow: 0 0 0 2px rgba(163,201,201,0.2);
        }
        .modal-footer {
            background-color: #fff;
            border-top: 1px solid #e5e7eb;
            padding: 1.25rem 1.5rem;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        .modal-footer .btn-primary {
            background-color: #6b7280;
            color: #fff;
            border-radius: 10px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        .modal-footer .btn-primary:hover {
            background-color: #4b5563;
        }
        .modal-footer .btn-secondary {
            background-color: #f3f4f6;
            color: #4b453f;
            border-radius: 10px;
            padding: 0.6rem 1.2rem;
            transition: background-color 0.2s ease;
        }
        .modal-footer .btn-secondary:hover {
            background-color: #e5e7eb;
        }
    </style>
</head>
<body>
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

    <div class="summary-cards">
        <div class="summary-card">
            <h5>Доход</h5>
            <p style="color:#16a34a;"><?= number_format($summary['income'] ?? 0, 2) ?> <?= $currencySymbols[$userCurrency] ?? '' ?></p>
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

    <button class="btn-add" id="createTransactionBtn" data-bs-toggle="modal" data-bs-target="#transactionModal">Создать транзакцию</button>
    <button class="btn-add" id="createRecurringBtn" data-bs-toggle="modal" data-bs-target="#transactionModal">Создать повторяющуюся транзакцию</button>

    <div class="transactions-container">
        <?php if (isset($dataProvider) && $dataProvider->models): ?>
            <?php foreach ($dataProvider->models as $transaction): ?>
                <div class="transaction-card" data-id="<?= $transaction->id ?>">
                    <div class="transaction-info">
                        <p><strong>Дата:</strong> <?= Html::encode($transaction->date) ?></p>
                        <p><strong>Сумма:</strong> <?= Html::encode($transaction->display_amount ?? number_format($transaction->amount, 2)) ?> <?= $currencySymbols[$transaction->display_currency ?? $transaction->currency ?? $userCurrency] ?? '' ?></p>
                        <p><strong>Тип:</strong> <?= Html::encode($transaction->category->type ?? '-') ?></p>
                        <p><strong>Категория:</strong> <?= Html::encode($transaction->category->name ?? '-') ?></p>
                        <p><strong>Описание:</strong> <?= Html::encode($transaction->description ?? '-') ?></p>
                        <?php if ($transaction->recurring_id): ?>
                            <p><strong>Повтор:</strong> <?= Html::encode($transaction->recurringTransaction->displayFrequency()) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="transaction-actions">
                        <button class="editBtn js-update" data-id="<?= $transaction->id ?>">✏️</button>
                        <button class="deleteBtn js-delete" data-id="<?= $transaction->id ?>">🗑️</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет доступных транзакций.</p>
        <?php endif; ?>
    </div>
</div>

<?= $this->render('_modal', ['goals' => $goals]) ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('transactionModal');
        const modal = new bootstrap.Modal(modalEl);
        const form = document.getElementById('transactionForm');
        const formErrors = document.getElementById('formErrors');
        let currentAction = 'create';
        let currentId = null;

        const createUrl = '<?= $createUrl ?>';
        const updateUrl = '<?= $updateUrl ?>';
        const deleteUrl = '<?= $deleteUrl ?>';
        const viewUrl = '<?= $viewUrl ?>';
        const createRecurringUrl = '<?= $createRecurringUrl ?>';

        const currencySymbols = <?= json_encode($currencySymbols) ?>;
        const userCurrency = '<?= $userCurrency ?>';

        const currencyInput = document.createElement('input');
        currencyInput.type = 'hidden';
        currencyInput.name = 'Transaction[currency]';
        form.appendChild(currencyInput);

        // Создание транзакции
        document.getElementById('createTransactionBtn').addEventListener('click', () => {
            currentAction = 'create';
            currentId = null;
            form.reset();
            currencyInput.value = userCurrency;
            formErrors.textContent = '';
            formErrors.style.display = 'none';
            modalEl.querySelector('.modal-title').textContent = 'Создать транзакцию';
            modal.show();
        });

        document.getElementById('createRecurringBtn').addEventListener('click', () => {
            currentAction = 'createRecurring';
            currentId = null;
            form.reset();
            currencyInput.value = userCurrency;
            formErrors.textContent = '';
            formErrors.style.display = 'none';
            modalEl.querySelector('.modal-title').textContent = 'Создать повторяющуюся транзакцию';
            modal.show();
        });

        // Делегирование кликов на редактирование и удаление
        document.querySelector('.transactions-container').addEventListener('click', (e) => {
            const target = e.target;

            if (target.classList.contains('js-update')) {
                const id = target.dataset.id;
                fetch(`${viewUrl}?id=${id}`)
                    .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
                    .then(data => {
                        if (data.success && data.transaction) {
                            const t = data.transaction;
                            form.querySelector('[name="Transaction[amount]"]').value = t.display_amount;
                            form.querySelector('[name="Transaction[date]"]').value = t.date;
                            form.querySelector('[name="Transaction[category_id]"]').value = t.category_id;
                            form.querySelector('[name="Transaction[goal_id]"]').value = t.goal_id || '';
                            form.querySelector('[name="Transaction[description]"]').value = t.description || '';
                            currencyInput.value = t.display_currency;
                            currentAction = 'update';
                            currentId = id;
                            formErrors.textContent = '';
                            formErrors.style.display = 'none';
                            modalEl.querySelector('.modal-title').textContent = 'Обновить транзакцию';
                            modal.show();
                        }
                        else {
                            formErrors.textContent = data.message || 'Ошибка при загрузке транзакции';
                            formErrors.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        console.error('Edit error:', err);
                        formErrors.textContent = 'Ошибка: ' + err.message;
                        formErrors.style.display = 'block';
                    });
            }

            if (target.classList.contains('js-delete')) {
                const id = target.dataset.id;
                if (!confirm('Удалить эту транзакцию?')) return;

                fetch(`${deleteUrl}?id=${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            target.closest('.transaction-card').remove();
                        } else {
                            alert('Ошибка при удалении: ' + (data.message || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(err => {
                        alert('Ошибка сети: ' + err.message);
                    });
            }
        });

        // Сохранение транзакции
        document.querySelector('.saveTransaction').addEventListener('click', () => {
            currencyInput.value = form.querySelector('#currencySelector')?.value || userCurrency; // если есть селектор валюты
            const formData = new FormData(form);
            const url = currentAction === 'create' ? createUrl :
                currentAction === 'createRecurring' ? createRecurringUrl :
                    `${updateUrl}?id=${currentId}`;

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
            })
                .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
                .then(data => {
                    if (data.success || data.id) {
                        modal.hide();
                        location.reload();
                    } else {
                        formErrors.textContent = data.message || Object.values(data.errors || {}).flat().join('; ') || 'Ошибка при сохранении';
                        formErrors.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Save error:', err);
                    formErrors.textContent = 'Ошибка: ' + err.message;
                    formErrors.style.display = 'block';
                });
        });
    });
</script>
</body>
</html>