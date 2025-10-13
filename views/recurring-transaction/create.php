<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RecurringTransaction $model */

$this->title = 'Create Recurring Transaction';
$this->params['breadcrumbs'][] = ['label' => 'Recurring Transactions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="recurring-transaction-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
