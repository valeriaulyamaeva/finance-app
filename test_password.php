<?php
require __DIR__ . '/vendor/autoload.php';
use Yii;
Yii::setAlias('@app', __DIR__);
$app = require __DIR__ . '/config/web.php';
Yii::$app->setComponents($app['components']);

$password = 'Gnarly25'; // Password used during login attempt
$hash = '$2y$13$Chtn5R4qp0cE2R0ezjB7T.TAuRzHywiBCazgoebpdI2ctMoJlxGpW'; // From DB for katseye@gmail.com
$isValid = Yii::$app->security->validatePassword($password, $hash);
var_dump($isValid); // Should output: bool(true) if password matches