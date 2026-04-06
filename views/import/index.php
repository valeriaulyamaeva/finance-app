<?php
/** @var yii\web\View $this */
/** @var app\models\Category[] $categories */

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = 'Импорт выписки';
$this->registerCssFile('@web/css/notifications.css');
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
AppAsset::register($this);

$categoriesData = array_map(fn($c) => [
    'id' => $c->id,
    'name' => $c->name,
    'type' => $c->type,
], $categories);

$urls = [
    'parse' => Url::to(['import/parse']),
    'confirm' => Url::to(['import/confirm']),
];

$this->registerJs('const importConfig = ' . json_encode([
    'urls' => $urls,
    'categories' => $categoriesData,
]) . ';', View::POS_HEAD);

$this->registerCssFile('@web/css/import.css');
$this->registerJsFile('@web/js/import.js', ['depends' => [JqueryAsset::class]]);
?>

<button class="sidebar-toggle d-lg-none" id="sidebarToggle">
    <i class="fas fa-bars fa-2x"></i>
</button>

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
        <li><a href="/category">Категории</a></li>
        <li><a href="/goal">Цели</a></li>
        <li><a href="/import">Импорт выписки</a></li>
        <li><a href="/settings">Настройки</a></li>
    </ul>
</div>

<div class="import-content" id="mainContent">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="upload-zone" id="uploadZone">
        <i class="fas fa-file-upload"></i>
        <p>Перетащите файл выписки сюда или нажмите для выбора</p>
        <p class="upload-hint">Поддерживаются форматы CSV и PDF</p>
        <input type="file" id="fileInput" accept=".csv,.pdf" style="display: none;">
    </div>

    <div class="preview-container" id="previewContainer">
        <div class="preview-header">
            <h3>Предпросмотр транзакций</h3>
            <div class="preview-stats">
                Всего: <span id="statsTotal">0</span> |
                Новых: <span id="statsNew">0</span> |
                Дубликатов: <span id="statsDuplicates">0</span>
            </div>
        </div>

        <div class="preview-table-wrapper">
            <table class="preview-table">
                <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" checked></th>
                    <th>Дата</th>
                    <th>Сумма</th>
                    <th>Описание</th>
                    <th>Тип операции</th>
                    <th>MCC</th>
                    <th>Категория</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody id="previewBody"></tbody>
            </table>
        </div>

        <div class="import-actions">
            <button class="btn-import" id="importBtn">Импортировать выбранные</button>
            <button class="btn-cancel" id="cancelBtn">Отмена</button>
        </div>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>
