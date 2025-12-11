<?php
return [
    'id' => 'app-test',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=mysql;dbname=finance_test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
        ],
    ],
];