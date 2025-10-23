<?php
/** @var yii\web\View $this */
/** @var string $content */

$this->registerCsrfMetaTags();
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

$theme = Yii::$app->view->params['theme'] ?? (Yii::$app->user->identity->theme ?? 'light');
$bodyClass = $theme === 'dark' ? 'theme-dark' : 'theme-light';
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= $this->render('_head') ?>
    <title></title></head>
<body class="<?= $bodyClass ?>">
<?php $this->beginBody() ?>

<?php if (Yii::$app->user->isGuest): ?>
    <main>
        <?= $content ?>
    </main>
<?php else: ?>
    <div class="sidebar">
        <?= $this->render('_sidebar') ?>
    </div>
    <main class="with-sidebar">
        <?= $content ?>
    </main>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
