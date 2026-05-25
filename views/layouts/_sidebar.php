<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>
<h2>PastelFinance</h2>

<ul>
    <li><a href="<?= Url::to(['/analytics']) ?>">Аналитика</a></li>
    <li><a href="<?= Url::to(['/transaction']) ?>">Транзакции</a></li>
    <li><a href="<?= Url::to(['/budget']) ?>">Бюджеты</a></li>
    <li><a href="<?= Url::to(['/category']) ?>">Категории</a></li>
    <li><a href="<?= Url::to(['/goal']) ?>">Цели</a></li>
    <li><a href="<?= Url::to(['/import']) ?>">Импорт выписки</a></li>
    <li><a href="<?= Url::to(['/settings']) ?>">Настройки</a></li>
</ul>

<?= Html::a('Выйти', ['/site/logout'], ['class' => 'btn btn-logout']) ?>

