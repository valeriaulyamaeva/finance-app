<?php
/** @var yii\web\View $this */
/** @var app\models\User $user */

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;
use yii\web\JqueryAsset;
use yii\web\View;

AppAsset::register($this);
$this->title = 'Настройки профиля';
$saveUrl = Url::to(['settings/save']);
?>

<button class="sidebar-toggle d-lg-none" id="sidebarToggle">
    <i class="fas fa-bars fa-2x"></i>
</button>

<div class="settings-page">
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
            <li><a href="/settings" class="active">Настройки</a></li>
        </ul>
    </div>

    <div class="settings-content" id="mainContent">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="settings-card">
            <div class="form-group">
                <label for="username">Имя</label>
                <input type="text" id="username" value="<?= Html::encode($user->username) ?>">
            </div>

            <div class="form-group">
                <label for="email">Электронная почта</label>
                <input type="email" id="email" value="<?= Html::encode($user->email) ?>">
            </div>

            <div class="form-group">
                <label for="password">Новый пароль <small>(оставьте пустым, если не хотите менять)</small></label>
                <input type="password" id="password" placeholder="••••••••">
            </div>

            <div class="form-group">
                <label for="theme">Тема интерфейса</label>
                <select id="theme">
                    <option value="light" <?= $user->theme === 'light' ? 'selected' : '' ?>>Светлая</option>
                    <option value="dark" <?= $user->theme === 'dark' ? 'selected' : '' ?>>Тёмная</option>
                </select>
            </div>

            <div class="form-group">
                <label for="currency">Основная валюта</label>
                <select id="currency">
                    <option value="BYN" <?= $user->currency === 'BYN' ? 'selected' : '' ?>>Белорусский рубль (BYN)</option>
                    <option value="USD" <?= $user->currency === 'USD' ? 'selected' : '' ?>>Доллар США (USD)</option>
                    <option value="EUR" <?= $user->currency === 'EUR' ? 'selected' : '' ?>>Евро (EUR)</option>
                    <option value="RUB" <?= $user->currency === 'RUB' ? 'selected' : '' ?>>Российский рубль (RUB)</option>
                </select>
            </div>

            <button class="btn-save" id="saveSettingsBtn">Сохранить изменения</button>
            <div class="message" id="saveMessage">Настройки успешно сохранены!</div>
        </div>
    </div>
</div>

<?php
$this->registerJs("const saveUrl = '$saveUrl'; const userTheme = '{$user->theme}';", View::POS_HEAD);

$this->registerCssFile('@web/css/settings.css');
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@web/js/settings.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@web/js/sidebar.js', ['depends' => JqueryAsset::class]);
?>
