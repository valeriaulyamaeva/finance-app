<?php
use yii\helpers\Html;

$this->registerMetaTag(['charset' => Yii::$app->charset]);
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
?>
<title><?= Html::encode($this->title) ?></title>
<?php $this->head() ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<?php
$themePath = Yii::getAlias('@webroot/css/theme.css');
$uiPath = Yii::getAlias('@webroot/css/ui.css');
$themeVer = file_exists($themePath) ? filemtime($themePath) : '1';
$uiVer = file_exists($uiPath) ? filemtime($uiPath) : '1';
?>
<link rel="stylesheet" href="<?= Yii::getAlias('@web/css/theme.css') ?>?v=<?= $themeVer ?>">
<link rel="stylesheet" href="<?= Yii::getAlias('@web/css/ui.css') ?>?v=<?= $uiVer ?>">
