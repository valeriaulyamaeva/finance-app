<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\models\Category;

$this->title = 'Бюджеты';
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['position' => View::POS_END]);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$createUrl = Url::to(['budget/create']);
$updateUrl = Url::to(['budget/update']);
$deleteUrl = Url::to(['budget/delete']);
$viewUrl = Url::to(['budget/view']);

$currencySymbols = [
    'BYN' => 'Br',
    'EUR' => '€',
    'USD' => '$',
    'RUB' => '₽',
];
$userCurrency = $user->currency ?? 'BYN';
$currencySymbol = $currencySymbols[$userCurrency] ?? $userCurrency;
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
        }
        .content h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
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
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(22rem, 1fr));
            gap: 1.5rem;
        }
        .card {
            background: #fff;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 0;
            font-size: 1.3rem;
            color: #4b453f;
        }
        .card p {
            margin: 0.25rem 0;
            color: #6b7280;
        }
        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        .actions button {
            border: none;
            background: none;
            cursor: pointer;
            color: #6b7280;
            font-size: 1rem;
        }
        .actions button:hover { color: #171716; }

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
        #formErrors {
            color: #dc2626;
            display: none;
            margin-bottom: 1rem;
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
    <h1>Бюджеты</h1>
    <div class="summary-cards">
        <div class="summary-card">
            <h5>Всего бюджет</h5>
            <p><?= number_format($summary['total_budget'] ?? 0, 2) ?> <?= Html::encode($currencySymbol) ?></p>
        </div>
        <div class="summary-card">
            <h5>Потрачено</h5>
            <p style="color:#dc2626;"><?= number_format($summary['total_spent'] ?? 0, 2) ?> <?= Html::encode($currencySymbol) ?></p>
        </div>
        <div class="summary-card">
            <h5>Остаток</h5>
            <p style="color:#16a34a;"><?= number_format($summary['remaining'] ?? 0, 2) ?> <?= Html::encode($currencySymbol) ?></p>
        </div>
    </div>

    <button class="btn-add" id="addBudgetBtn" data-bs-toggle="modal" data-bs-target="#budgetModal">
        Добавить бюджет
    </button>

    <div class="cards-container">
        <?php if (isset($budgetsWithDisplay) && $budgetsWithDisplay): ?>
            <?php foreach ($budgetsWithDisplay as $budgetData): ?>
                <?php $budget = $budgetData['model']; ?>
                <div class="card" data-id="<?= $budget->id ?>">
                    <div>
                        <h3><?= Html::encode($budget->name) ?></h3>
                        <p>Категория: <?= Html::encode($budget->category->name ?? '-') ?></p>
                        <p>Сумма: <?= Html::encode($budgetData['display_amount']) ?> <?= Html::encode($budgetData['display_currency'] ?? $currencySymbol) ?></p>
                        <p>Потрачено: <?= Html::encode($budgetData['display_spent']) ?> <?= Html::encode($budgetData['display_currency']) ?></p>
                        <p>Остаток: <?= Html::encode($budgetData['display_remaining']) ?> <?= Html::encode($budgetData['display_currency']) ?></p>
                        <p>Период: <?= Html::encode($budget->displayPeriod()) ?></p>
                        <p>Срок: <?= Html::encode($budget->start_date) ?> → <?= Html::encode($budget->end_date) ?></p>
                    </div>
                    <div class="actions">
                        <button class="editBtn" data-id="<?= $budget->id ?>">✏️</button>
                        <button class="deleteBtn" data-id="<?= $budget->id ?>">🗑️</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет доступных бюджетов.</p>
        <?php endif; ?>
    </div>
</div>

<?= $this->render('_budgetModal', ['categories' => ArrayHelper::map(Category::find()->all(), 'id', 'name')]) ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('budgetModal');
        const modal = new bootstrap.Modal(modalEl);
        const form = document.getElementById('budgetForm');
        const formErrors = document.getElementById('formErrors');
        const budgetCurrency = document.getElementById('budgetCurrency');
        let currentAction = 'create';
        let currentId = null;

        const createUrl = '<?= $createUrl ?>';
        const updateUrl = '<?= $updateUrl ?>';
        const deleteUrl = '<?= $deleteUrl ?>';
        const viewUrl = '<?= $viewUrl ?>';
        const currencySymbol = '<?= Html::encode($currencySymbol) ?>';

        // Создание бюджета
        document.getElementById('addBudgetBtn').addEventListener('click', () => {
            currentAction = 'create';
            currentId = null;
            form.reset();
            budgetCurrency.value = '<?= $userCurrency ?>';
            formErrors.textContent = '';
            formErrors.style.display = 'none';
            modal.show();
        });

        // Делегирование кликов по кнопкам редактирования и удаления
        document.querySelector('.cards-container').addEventListener('click', (e) => {
            const target = e.target;

            // Редактирование
            if (target.classList.contains('editBtn')) {
                const id = target.dataset.id;
                fetch(`${viewUrl}?id=${id}`)
                    .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
                    .then(data => {
                        if (data.success && data.budget) {
                            const b = data.budget;
                            form.querySelector('[name="Budget[name]"]').value = b.name;
                            form.querySelector('[name="Budget[amount]"]').value = b.display_amount;
                            form.querySelector('[name="Budget[period]"]').value = b.period;
                            form.querySelector('[name="Budget[category_id]"]').value = b.category_id;
                            form.querySelector('[name="Budget[start_date]"]').value = b.start_date;
                            form.querySelector('[name="Budget[end_date]"]').value = b.end_date;
                            budgetCurrency.value = b.display_currency || 'BYN';
                            currentAction = 'update';
                            currentId = id;
                            formErrors.textContent = '';
                            formErrors.style.display = 'none';
                            modal.show();
                        } else {
                            formErrors.textContent = data.message || 'Ошибка при загрузке бюджета';
                            formErrors.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        console.error('Edit error:', err);
                        formErrors.textContent = `Ошибка: ${err.message}`;
                        formErrors.style.display = 'block';
                    });
            }

            // Удаление
            if (target.classList.contains('deleteBtn')) {
                if (!confirm('Удалить бюджет?')) return;
                const id = target.dataset.id;

                fetch(deleteUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({ id })
                })
                    .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            formErrors.textContent = data.message || 'Ошибка при удалении';
                            formErrors.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        formErrors.textContent = `Ошибка: ${err.message}`;
                        formErrors.style.display = 'block';
                    });
            }
        });

        // Сохранение бюджета
        document.querySelector('.saveBudget').addEventListener('click', () => {
            const formData = new FormData(form);
            formData.set('Budget[currency]', budgetCurrency.value);
            const url = currentAction === 'create' ? createUrl : `${updateUrl}?id=${currentId}`;

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
            })
                .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
                .then(data => {
                    if (data.success && data.budget) {
                        location.reload(); // можно заменить на динамическое обновление карточки
                    } else {
                        formErrors.textContent = data.message || 'Ошибка при сохранении';
                        formErrors.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Save error:', err);
                    formErrors.textContent = `Ошибка: ${err.message}`;
                    formErrors.style.display = 'block';
                });
        });
    });
</script>
</body>
</html>