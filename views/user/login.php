<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\forms\UserLoginForm;

/** @var yii\web\View $this */
/** @var UserLoginForm $model */

$this->title = 'Вход';
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
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
        .login-container {
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
        .btn-login {
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
        .btn-login:hover {
            background-color: #9d8e87;
        }
        .register-link, .home-link {
            margin-top: 15px;
            color: #6b655d;
            display: block;
            font-size: 0.95rem;
            text-decoration: none;
        }
        .home-link:hover, .register-link a:hover {
            text-decoration: underline;
        }
        .home-link {
            margin-bottom: 10px;
        }
        .alert {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.95rem;
            text-align: left;
        }
        .alert-success { background-color: #d6f0e1; color: #2b5d3b; }
        .alert-danger { background-color: #f8d6d6; color: #842029; }

        .field-userloginform-rememberme { text-align: left; }
    </style>
</head>
<body>
<div class="login-container">
    <h1>Вход</h1>

    <?= Html::a('← На главную', ['/site/index'], ['class' => 'home-link']) ?>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'fieldConfig' => [
            'template' => "{input}\n{error}",
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'text-danger small', 'style' => 'color: #842029; margin-top: 5px;'],
        ],
    ]); ?>

    <div class="form-group">
        <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'placeholder' => 'Электронная почта']) ?>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Пароль']) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'rememberMe')->checkbox([
            'template' => "<div class=\"custom-control custom-checkbox\">{input} {label}</div>\n{error}",
        ]) ?>
    </div>

    <?= Html::submitButton('Войти', ['class' => 'btn-login']) ?>

    <div class="register-link">
        Нет аккаунта? <?= Html::a('Зарегистрироваться', ['user/register']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
</body>
</html>