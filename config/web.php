<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'container' => require __DIR__ . '/di.php',
    'components' => [
        'request' => [
            'cookieValidationKey' => 'fVLhdUkDMa9mEaqUdkr1toumhJ_aj-ss',
            'class' => 'yii\web\Request',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@runtime/cache',
            'keyPrefix' => 'pastelfinance_',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'class' => 'yii\web\User',
            'loginUrl' => ['site/login'],
        ],
        'recurringTransactionService' => [
            'class' => 'app\services\RecurringTransactionService',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 10 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/debug.log',
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',

                'login' => 'site/login',
                'logout' => 'site/logout',
                'register' => 'site/register',
                'settings' => 'settings/index',
                'settings/save' => 'settings/save',
                'import' => 'import/index',
                'import/<action:\w+>' => 'import/<action>',
                'forecast' => 'forecast/index',
                'investment' => 'investment/index',

                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
        'assetManager' => [
            'bundles' => [
                'yii\bootstrap5\BootstrapAsset' => [
                    'css' => ['css/bootstrap.min.css'],
                    'js' => ['js/bootstrap.bundle.min.js'],
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV && getenv('YII_DEBUG_TOOLBAR') !== '0') {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
    ];
}

return $config;