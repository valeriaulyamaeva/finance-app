<?php
return [
    'bundles' => [
        'yii\web\JqueryAsset' => false,
        'yii\bootstrap5\BootstrapAsset' => false,
        'yii\bootstrap5\BootstrapPluginAsset' => false,
    ],
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => ['all.js'],
            'css' => ['all.css'],
        ],
    ],
    'assetManager' => [
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
    ],
];