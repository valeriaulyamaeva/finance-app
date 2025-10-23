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

    <div class="settings-form">
        <label>Имя</label>
        <label for="username"></label><input type="text" id="username" value="<?= Html::encode($user->username) ?>">

        <label>Электронная почта</label>
        <label for="email"></label><input type="email" id="email" value="<?= Html::encode($user->email) ?>">

        <label>Пароль (оставьте пустым, если не нужно менять)</label>
        <label for="password"></label><input type="password" id="password">

        <label>Тема</label>
        <label for="theme"></label><select id="theme">
            <option value="light" <?= $user->theme === 'light' ? 'selected' : '' ?>>Светлая</option>
            <option value="dark" <?= $user->theme === 'dark' ? 'selected' : '' ?>>Тёмная</option>
        </select>

        <label>Валюта</label>
        <label for="currency"></label><select id="currency">
            <option value="BYN" <?= $user->currency === 'BYN' ? 'selected' : '' ?>>Бел. рубль (BYN)</option>
            <option value="USD" <?= $user->currency === 'USD' ? 'selected' : '' ?>>Доллар (USD)</option>
            <option value="EUR" <?= $user->currency === 'EUR' ? 'selected' : '' ?>>Евро (EUR)</option>
            <option value="RUB" <?= $user->currency === 'RUB' ? 'selected' : '' ?>>Рубль (RUB)</option>
        </select>

        <button class="btn-save" id="saveSettingsBtn">Сохранить</button>
        <p class="message" id="saveMessage">Настройки сохранены</p>
    </div>
</div>

<?php
$this->registerJs("const saveUrl = '$saveUrl'; const userTheme = '{$user->theme}';", View::POS_HEAD);

$this->registerCssFile('@web/css/settings.css');
$this->registerJsFile('@web/js/settings.js', ['depends' => [JqueryAsset::class]]);
?>
