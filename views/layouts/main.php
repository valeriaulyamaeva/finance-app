<?php
/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset]);
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #dfdfdf;
            color: #222;
        }
        header, footer {
            background: #000;
            color: #fff;
            padding: 1rem 2rem;
        }
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<main>
    <?= $content ?>
</main>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
