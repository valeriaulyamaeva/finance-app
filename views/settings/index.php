<?php
/** @var yii\web\View $this */
/** @var app\models\User $user */

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;

AppAsset::register($this);
$this->title = 'Настройки профиля';
$saveUrl = Url::to(['settings/save']);
$theme = $user->theme;
$bodyBg = $theme === 'dark' ? '#1f1f1f' : '#f9f7f4';
$bodyColor = $theme === 'dark' ? '#f3f3f3' : '#4b453f';
$sidebarBg = $theme === 'dark' ? '#333' : '#b6b6b6';
$formBg = $theme === 'dark' ? '#2a2a2a' : '#fff';
$formInputBorder = $theme === 'dark' ? '#4b5563' : '#d1d5db';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= Yii::$app->request->csrfToken ?>">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: <?= $bodyBg ?>;
            color: <?= $bodyColor ?>;
            margin: 0;
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
        .settings-form {
            background: <?= $formBg ?>;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 600px;
        }
        input, select {
            width: 100%;
            padding: 0.6rem;
            border-radius: 10px;
            border: 1px solid <?= $formInputBorder ?>;
            margin-bottom: 1rem;
            background-color: <?= $theme === 'dark' ? '#374151' : '#fff' ?>;
            color: <?= $theme === 'dark' ? '#f3f3f3' : '#4b453f' ?>;
        }
        .btn-save {
            background-color: #a3c9c9;
            border: none;
            border-radius: 10px;
            padding: 0.8rem 1.2rem;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-save:hover {
            background-color: #8da4a4;
            color: #fff;
        }
        .message {
            margin-top: 1rem;
            color: #16a34a;
            display: none;
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
        <p class="message" id="saveMessage">Настройки сохранены ✅</p>
    </div>
</div>

<script>
    document.getElementById('saveSettingsBtn').addEventListener('click', async () => {
        const formData = new FormData();
        formData.append('User[username]', document.getElementById('username').value);
        formData.append('User[email]', document.getElementById('email').value);
        formData.append('User[password]', document.getElementById('password').value);
        formData.append('User[theme]', document.getElementById('theme').value);
        formData.append('User[currency]', document.getElementById('currency').value);

        const res = await fetch('<?= $saveUrl ?>', {
            method: 'POST',
            body: formData,
            headers: {'X-CSRF-Token': document.querySelector("meta[name='csrf-token']").content}
        });
        const data = await res.json();

        const msg = document.getElementById('saveMessage');
        if (data.success) {
            msg.style.display = 'block';
            msg.style.color = '#16a34a';
            setTimeout(() => location.reload(), 1000);
        } else {
            msg.style.display = 'block';
            msg.style.color = '#dc2626';
            msg.textContent = data.message || 'Ошибка при сохранении';
        }
    });
</script>
</body>
</html>