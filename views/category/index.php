<?php
/** @var yii\web\View $this */
/** @var app\models\Category[] $categories */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = 'Категории';

// URL для операций
$createUrl = Url::to(['category/create']);
$updateUrl = Url::to(['category/update']);
$deleteUrl = Url::to(['category/delete']);

$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['position' => View::POS_END]);

$this->registerCssFile('@web/css/category.css');
$this->registerJsFile('@web/js/category.js', [
    'depends' => [JqueryAsset::class],
    'position' => View::POS_END,
]);

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
                    <label for="categoryName"></label><input type="text" id="categoryName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Тип</label>
                    <label for="categoryType"></label><select id="categoryType" class="form-select">
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
$urls = [
    'create' => $createUrl,
    'update' => $updateUrl,
    'delete' => $deleteUrl,
];
$this->registerJs('const categoryUrls = ' . json_encode($urls) . ';', View::POS_HEAD);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [
    'depends' => [JqueryAsset::class],
]);
?>
