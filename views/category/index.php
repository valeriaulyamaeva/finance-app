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
?>
<head>
    <meta name="csrf-token" content="<?= Yii::$app->request->csrfToken ?>">
    <title></title>
</head>

<div class="container mt-4">
    <h1><?= Html::encode($this->title) ?></h1>

    <button class="btn btn-primary mb-3" id="addCategoryBtn" data-bs-toggle="modal" data-bs-target="#categoryModal">
        Добавить категорию
    </button>

    <table class="table table-striped" id="categoriesTable"
           data-create-url="<?= $createUrl ?>"
           data-update-url="<?= $updateUrl ?>"
           data-delete-url="<?= $deleteUrl ?>">
        <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Тип</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $category): ?>
            <tr data-id="<?= $category->id ?>">
                <td><?= $category->id ?></td>
                <td class="name"><?= Html::encode($category->name) ?></td>
                <td class="type"><?= Html::encode($category->displayType()) ?></td>
                <td>
                    <button class="btn btn-sm btn-warning editBtn" data-id="<?= $category->id ?>">Редактировать</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="<?= $category->id ?>">Удалить</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="categoryForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Категория</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="categoryId">
                <div class="mb-3">
                    <label for="categoryName" class="form-label">Название</label>
                    <input type="text" class="form-control" id="categoryName" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="categoryType" class="form-label">Тип</label>
                    <select class="form-select" id="categoryType" name="type">
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

const table = document.getElementById('categoriesTable');
const createUrl = table.dataset.createUrl;
const updateUrl = table.dataset.updateUrl;
const deleteUrl = table.dataset.deleteUrl;

function bindRowEvents(row) {
    const id = row.dataset.id;

    // Редактировать
    row.querySelector('.editBtn').addEventListener('click', () => {
        currentAction = 'update';
        document.getElementById('categoryId').value = id;
        document.getElementById('categoryName').value = row.querySelector('.name').textContent;
        document.getElementById('categoryType').value = row.querySelector('.type').textContent.toLowerCase();
        document.getElementById('formErrors').textContent = '';
        modal.show();
    });

    // Удалить
    row.querySelector('.deleteBtn').addEventListener('click', () => {
        if (!confirm('Вы уверены, что хотите удалить категорию?')) return;

        fetch(deleteUrl + '?id=' + id, {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content}
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) row.remove();
        });
    });
}

// Привязываем события к существующим строкам
document.querySelectorAll('#categoriesTable tbody tr').forEach(bindRowEvents);

// Открытие модального окна для создания новой категории
document.getElementById('addCategoryBtn').addEventListener('click', () => {
    currentAction = 'create';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('formErrors').textContent = '';
});

// Отправка формы
document.getElementById('categoryForm').addEventListener('submit', e => {
    e.preventDefault();

    const formData = new FormData();
    formData.append('Category[name]', document.getElementById('categoryName').value);
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
            let row = currentAction === 'create' ? document.createElement('tr') : document.querySelector('tr[data-id="' + data.id + '"]');
            row.dataset.id = data.id;
row.innerHTML =
    '<td>' + data.id + '</td>' +
    '<td class="name">' + data.name + '</td>' +
    '<td class="type">' + data.type + '</td>' +
    '<td>' +
        '<button class="btn btn-sm btn-warning editBtn" data-id="' + data.id + '">Редактировать</button>' +
        '<button class="btn btn-sm btn-danger deleteBtn" data-id="' + data.id + '">Удалить</button>' +
    '</td>';
            if (currentAction === 'create') {
                document.querySelector('#categoriesTable tbody').prepend(row);
            }
            bindRowEvents(row);
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
