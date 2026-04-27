# DI Container

Yii DI Container настроен в `config/di.php` и подключается в `web.php` + `console.php`.

## Регистрация

```php
// config/di.php
return [
    'definitions' => [
        // Per-request instances
        TransactionService::class => TransactionService::class,
        ImportService::class => ImportService::class,
        // ...
    ],
    'singletons' => [
        // App-wide single instance
        CurrencyService::class => CurrencyService::class,
    ],
];
```

## Использование

### В контроллере (constructor injection)

```php
final class TransactionController extends BaseController
{
    public function __construct(
        $id, $module,
        private readonly TransactionService $service,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }
}
```

### Вручную через контейнер

```php
$service = Yii::$container->get(TransactionService::class);
```

## Console DI

В `config/console.php` подключается тот же `di.php`:

```php
'container' => require __DIR__ . '/di.php',
```

Это нужно для `commands/RecurringTransactionController` (cron) и других CLI-команд.

## См. также

- [[service-layer]]
- [[../infrastructure/cron-jobs]]
