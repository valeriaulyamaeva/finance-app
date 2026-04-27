<?php
/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;

$this->registerCsrfMetaTags();
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

$theme = 'light';
if (!Yii::$app->user->isGuest && Yii::$app->user->identity) {
    $theme = Yii::$app->user->identity->theme;
}
$bodyClass = $theme === 'dark' ? 'theme-dark' : 'theme-light';
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <?= $this->render('_head') ?>
        <title><?= Html::encode($this->title) ?></title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <button type="button" id="notificationBtn" class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span id="notificationCount" class="notification-count"></span>
                    </button>

                    <a href="<?= Url::to(['site/logout']) ?>" class="logout-btn" title="Выйти">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>

            <div class="content-wrapper" style="padding: 20px;">
                <?php if (Yii::$app->session->hasFlash('success')): ?>
                    <div class="alert alert-success" style="background:#d6f0e1; color:#2b5d3b; padding:15px; border-radius:10px; margin-bottom:20px;">
                        <?= Yii::$app->session->getFlash('success') ?>
                    </div>
                <?php endif; ?>
                <?php if (Yii::$app->session->hasFlash('error')): ?>
                    <div class="alert alert-danger" style="background:#f8d6d6; color:#842029; padding:15px; border-radius:10px; margin-bottom:20px;">
                        <?= Yii::$app->session->getFlash('error') ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </main>
    <?php endif; ?>

    <div id="notificationDropdown" class="notif-dropdown">
        <div class="notif-dropdown-header">
            <span>Уведомления</span>
            <button type="button" id="markAllReadBtn" class="notif-mark-all">Прочитать все</button>
        </div>
        <ul id="notificationList" class="notif-list"></ul>
        <div class="notif-empty" id="notifEmpty">Нет уведомлений</div>
    </div>

    <?php
    $this->registerCssFile('@web/css/notifications.css');
    $this->registerJsFile('@web/js/ui.js');
    $this->registerJsFile('@web/js/notifications.js', ['depends' => [JqueryAsset::class]]);
    ?>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>