<?php

return [
    'class' => 'yii\di\Container',
    'definitions' => [
        'app\services\CurrencyService' => 'app\services\CurrencyService',
        'app\services\BudgetService' => 'app\services\BudgetService',
        'app\services\GoalService' => 'app\services\GoalService',
        'app\services\TransactionService' => 'app\services\TransactionService',
        'app\services\RecurringTransactionService' => 'app\services\RecurringTransactionService',
    ],
    'singletons' => [
        'app\services\CurrencyService' => 'app\services\CurrencyService',
    ],
];