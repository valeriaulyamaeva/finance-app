<?php
/** @var yii\web\View $this */
/** @var app\models\Category[] $categories */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = 'Категории';

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

<button class="sidebar-toggle d-lg-none" id="sidebarToggle">
    <i class="fas fa-bars fa-2x"></i>
</button>

<div class="category-page">
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header d-flex justify-content-between align-items-center d-lg-none">
            <h2>PastelFinance</h2>
            <button class="sidebar-close" id="sidebarClose">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <h2 class="d-none d-lg-block">PastelFinance</h2>

        <ul>
            <li><a href="/analytics">Аналитика</a></li>
            <li><a href="/transaction">Транзакции</a></li>
            <li><a href="/budget">Бюджеты</a></li>
            <li><a href="/category" class="active">Категории</a></li>
            <li><a href="/goal">Цели</a></li>
            <li><a href="/settings">Настройки</a></li>
        </ul>
    </div>

    <div class="category-content" id="mainContent">
        <h1><?= Html::encode($this->title) ?></h1>
        <p class="text-muted mb-4">Управляйте своими категориями доходов и расходов</p>

        <div class="actions mb-4">
            <button class="btn-add" id="addCategoryBtn" data-bs-toggle="modal" data-bs-target="#categoryModal">
                Добавить категорию
            </button>
        </div>

        <div class="cards-container">
            <?php if ($categories): ?>
                <?php foreach ($categories as $category): ?>
                    <div class="card" data-id="<?= $category->id ?>">
                        <div class="card-body">
                            <h3><?= Html::encode($category->name) ?></h3>
                            <p class="text-muted"><?= Html::encode($category->displayType()) ?></p>
                        </div>
                        <div class="card-actions">
                            <button class="editBtn" title="Редактировать">✏️</button>
                            <button class="deleteBtn" title="Удалить">🗑️</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted py-5 fs-4">Категории ещё не созданы</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-gradient-primary text-white border-0 py-4">
                <h5 class="modal-title fw-bold fs-5 d-flex align-items-center gap-3" id="categoryModalLabel">
                    <i class="fas fa-tag"></i>
                    <span id="modalCategoryTitle">Новая категория</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <form id="categoryForm" class="modal-body p-4 p-lg-5">
                <input type="hidden" id="categoryId">

                <div class="form-floating mb-4">
                    <input type="text"
                           class="form-control rounded-3"
                           id="categoryName"
                           placeholder="Название категории"
                           required
                           maxlength="100">
                    <label for="categoryName">
                    </label>
                </div>

                <div class="form-floating mb-4">
                    <select class="form-select rounded-3" id="categoryType" required>
                        <option value="expense">Расход</option>
                        <option value="income">Доход</option>
                        <option value="goal">Цель</option>
                    </select>
                    <label for="categoryType">
                    </label>
                </div>

                <div id="formErrors" class="alert alert-danger mt-3 d-none"></div>
            </form>

            <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Отмена
                </button>
                <button type="submit" form="categoryForm" class="btn btn-primary rounded-pill px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i>
                    <span id="saveButtonText">Сохранить</span>
                </button>
            </div>
        </div>
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
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>
