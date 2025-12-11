<?php
defined('YII_TEST') or define('YII_TEST', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/test.php';

if (!file_exists(__DIR__ . '/../config/test.php')) {
    $dbConfig = require __DIR__ . '/../config/db.php';
    $dbConfig['dsn'] = 'mysql:host=mysql;dbname=finance_test';
    $dbConfig['username'] = 'root';
    $dbConfig['password'] = 'root';
    $dbConfig['charset'] = 'utf8';

    file_put_contents(__DIR__ . '/../config/test.php', "<?php\nreturn " . var_export(['components' => ['db' => $dbConfig]], true) . ";\n");
}

new yii\web\Application($config);