<?php
/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use yii\web\JqueryAsset;

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
    <title></title>
</head>
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
        <div class="top-bar">
            <div class="top-bar-right">
                <a href="javascript:void(0);" id="notificationBtn" class="notification-btn">
                    <i class="fa fa-bell"></i>
                    <span id="notificationCount" class="notification-count"></span>
                </a>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <form action="<?= Yii::$app->urlManager->createUrl(['site/logout']) ?>" method="post" style="display: inline;">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                        <button type="submit" class="logout-btn" style="background:none;border:none;padding:0;cursor:pointer;">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?= $content ?>
    </main>
<?php endif; ?>

<div id="notificationModal" class="notification-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Уведомления</h4>
            <span class="close">&times;</span>
        </div>
        <ul id="notificationList" class="notification-list"></ul>
        <div class="modal-footer" style="padding: 10px; text-align: right;">
            <button class="mark-all-read">Отметить все как прочитанные</button>
        </div>
    </div>
</div>

<?php
$this->registerCssFile('@web/css/notifications.css');
$this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
