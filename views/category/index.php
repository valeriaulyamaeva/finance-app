<?php
/** @var yii\web\View $this */
/** @var app\models\Category[] $categories */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Категории';
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['position' => View::POS_END]);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$createUrl = Url::to(['category/create']);
$updateUrl = Url::to(['category/update']);
$deleteUrl = Url::to(['category/delete']);
$viewUrl   = Url::to(['category/view']);
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
        .content p {
            color: #6b7280;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(22rem, 1fr));
            gap: 1.5rem;
        }
        .card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            justify-content: space-between;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 500;
        }
        .card p {
            margin: 0.25rem 0 0 0;
            font-size: 0.95rem;
            color: #6b7280;
        }
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        .actions button {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #6b7280;
            transition: color 0.2s ease;
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
        .btn-add:hover { background-color: #8da4a4; color: #fff; }

        .modal-dialog { max-width: 450px; margin: 2rem auto; }
        .modal-header { background-color: #fff; }
        .modal-title { font-size: 1.25rem; font-weight: 600; }
        .form-control, .form-select { border-radius: 10px; padding: 0.6rem 0.75rem; }
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
    <p>Управляйте своими категориями доходов и расходов</p>

    <button class="btn-add mb-3" id="addCategoryBtn" data-bs-toggle="modal" data-bs-target="#categoryModal">
        Добавить категорию
    </button>

    <div class="cards-container">
        <?php foreach ($categories as $category): ?>
            <div class="card" data-id="<?= $category->id ?>">
                <div>
                    <h3><?= Html::encode($category->name) ?></h3>
                    <p><?= Html::encode($category->displayType()) ?></p>
                </div>
                <div class="actions">
                    <button class="editBtn" title="Редактировать">✏️</button>
                    <button class="deleteBtn" title="Удалить">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="categoryForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Категория</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="categoryId">
                <div class="mb-3">
                    <label class="form-label">Название</label>
                    <input type="text" id="categoryName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Тип</label>
                    <select id="categoryType" class="form-select">
                        <option value="income">Доход</option>
                        <option value="expense">Расход</option>
                        <option value="goal">Цель</option>
                    </select>
                </div>
                <div id="formErrors" class="text-danger"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
            </div>
        </form>
    </div>
</div>

<?php
$js = <<<JS
const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
let currentAction = 'create';

const createUrl = '$createUrl';
const updateUrl = '$updateUrl';
const deleteUrl = '$deleteUrl';

function bindCardEvents(card) {
    const id = card.dataset.id;

    card.querySelector('.editBtn').addEventListener('click', () => {
        currentAction = 'update';
        document.getElementById('categoryId').value = id;
        document.getElementById('categoryName').value = card.querySelector('h3').textContent;
        document.getElementById('categoryType').value = card.querySelector('p').textContent.toLowerCase();
        document.getElementById('formErrors').textContent = '';
        modal.show();
    });

    card.querySelector('.deleteBtn').addEventListener('click', () => {
        if (!confirm('Вы уверены, что хотите удалить категорию?')) return;

        fetch(deleteUrl + '?id=' + id, {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content}
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) card.remove();
            else alert(data.message || 'Ошибка при удалении категории');
        })
        .catch(err => alert(err.message));
    });
}

// Привязываем события к существующим карточкам
document.querySelectorAll('.card').forEach(bindCardEvents);

// Открытие модалки для создания новой категории
document.getElementById('addCategoryBtn').addEventListener('click', () => {
    currentAction = 'create';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('formErrors').textContent = '';
});

// Сохранение категории
document.getElementById('categoryForm').addEventListener('submit', e => {
    e.preventDefault();

    const formData = new FormData();
    formData.append('Category[name]', document.getElementById('categoryName').value.trim());
    formData.append('Category[type]', document.getElementById('categoryType').value);
    formData.append('Category[id]', document.getElementById('categoryId').value);

    const url = currentAction === 'create' ? createUrl : updateUrl + '?id=' + formData.get('Category[id]');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-Token': csrfToken}
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            let card;
            if (currentAction === 'create') {
                card = document.createElement('div');
                card.classList.add('card');
                card.dataset.id = data.id;
                document.querySelector('.cards-container').prepend(card);
            } else {
                card = document.querySelector('.card[data-id="' + data.id + '"]');
            }

            // Формируем HTML карточки
            card.innerHTML =
                '<div>' +
                    '<h3>' + data.name + '</h3>' +
                    '<p>' + data.type.charAt(0).toUpperCase() + data.type.slice(1) + '</p>' +
                '</div>' +
                '<div class="actions">' +
                    '<button class="editBtn" title="Редактировать">✏️</button>' +
                    '<button class="deleteBtn" title="Удалить">🗑️</button>' +
                '</div>';

            bindCardEvents(card);
            modal.hide();
        } else if (data) {
            document.getElementById('formErrors').textContent = Object.values(data).flat().join('; ');
        }
    })
    .catch(err => {
        document.getElementById('formErrors').textContent = err.message;
    });
});
JS;

$this->registerJs($js);
?>

</html>