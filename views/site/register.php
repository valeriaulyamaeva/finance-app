<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = 'Регистрация';
$this->params['breadcrumbs'][] = $this->title;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: #f2f0eb;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .register-container {
            background: #fffaf8;
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            width: 30rem;
            max-width: 600px;
            text-align: center;
        }
        h1 {
            color: #4b453f;
            margin-bottom: 1rem;
            font-size: 2rem;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d8d2c4;
            border-radius: 12px;
            box-sizing: border-box;
            font-size: 1rem;
            background: #f9f6f2;
            color: #5a5045;
        }
        .form-control:focus {
            border-color: #a29f97;
            outline: none;
            box-shadow: 0 0 0 2px rgba(162, 159, 151, 0.2);
        }
        .btn-register {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background-color: #a3c9c9;
            color: #4b453f;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        .btn-register:hover {
            background-color: #9d8e87;
        }
        .login-link, .home-link {
            margin-top: 15px;
            color: #6b655d;
            display: block;
            font-size: 0.95rem;
        }
        .home-link {
            margin-bottom: 10px;
        }
        .alert {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.95rem;
        }
        .alert-success {
            background-color: #d6f0e1;
            color: #2b5d3b;
        }
        .alert-danger {
            background-color: #f8d6d6;
            color: #842029;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 2rem 1.5rem;
            }
            h1 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>
<div class="register-container">
    <h1>Регистрация</h1>

    <a class="home-link" href="/">← На главную</a>

    <?php $form = ActiveForm::begin([
        'id' => 'register-form',
        'fieldConfig' => [
            'template' => "{input}\n{error}",
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'text-danger small'],
        ],
    ]); ?>

    <div class="form-group">
        <?= $form->field($model, 'username')->textInput(['placeholder' => 'Имя']) ?>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Электронная почта']) ?>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Пароль']) ?>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder' => 'Подтвердите пароль']) ?>
    </div>

    <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn-register']) ?>

    <div class="login-link">
        Уже есть аккаунт? <?= Html::a('Войти', ['login']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
