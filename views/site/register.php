<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = 'Регистрация';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-register container mt-5">
    <div class="card p-4 shadow-sm" style="max-width: 400px; margin: 0 auto;">
        <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

        <p class="text-center mb-4">Пожалуйста, заполните поля для регистрации:</p>

        <?php $form = ActiveForm::begin([
            'id' => 'register-form',
            'fieldConfig' => [
                'template' => "{label}\n<div>{input}</div>\n{error}",
                'labelOptions' => ['class' => 'form-label'],
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'text-danger small'],
            ],
        ]); ?>

        <?= $form->field($model, 'username')->textInput(['placeholder' => 'Имя']) ?>
        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Электронная почта']) ?>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Пароль']) ?>
        <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder' => 'Подтвердите пароль']) ?>

        <div class="text-center mt-4">
            <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary w-100']) ?>
        </div>

        <p class="text-center mt-3">
            Уже есть аккаунт? <?= Html::a('Войти', ['login']) ?>
        </p>

        <?php ActiveForm::end(); ?>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success mt-3">
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger mt-3">
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>
    </div>
</div>