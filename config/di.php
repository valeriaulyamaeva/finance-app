<?php

use app\services\CategoryService;
use app\services\UserService;
use app\services\BudgetService;
use app\services\GoalService;
use app\services\TransactionService;
use app\services\RecurringTransactionService;
use app\services\CurrencyService;
use app\services\ImportService;
use app\services\NotificationService;
use app\services\KeycloakService;
use app\services\ForecastService;

return [
    'definitions' => [
        CategoryService::class => function () {
            return new CategoryService(Yii::$app->db);
        },

        UserService::class => function () {
            return new UserService(
                Yii::$app->db,
                Yii::$container->get(CategoryService::class)
            );
        },

        BudgetService::class => BudgetService::class,
        GoalService::class => GoalService::class,
        TransactionService::class => TransactionService::class,
        RecurringTransactionService::class => RecurringTransactionService::class,
        ImportService::class => ImportService::class,
        NotificationService::class => NotificationService::class,
        KeycloakService::class => KeycloakService::class,
        ForecastService::class => ForecastService::class,
    ],
    'singletons' => [
        CurrencyService::class => CurrencyService::class,
    ],
];