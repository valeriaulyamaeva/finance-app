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
        <?php /* Floating top-bar (bell + logout). The sidebar is rendered by each page view. */ ?>
        <div class="app-topbar">
            <button type="button" id="notificationBtn" class="notification-btn">
                <i class="fas fa-bell"></i>
                <span id="notificationCount" class="notification-count"></span>
            </button>
            <a href="<?= Url::to(['/site/logout']) ?>" class="logout-btn" title="Выйти">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="app-flash app-flash--success"><?= Yii::$app->session->getFlash('success') ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="app-flash app-flash--error"><?= Yii::$app->session->getFlash('error') ?></div>
        <?php endif; ?>

        <?= $content ?>
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