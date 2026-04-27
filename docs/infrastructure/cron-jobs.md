# Cron Jobs

Контейнер `cron` запускает Yii console-команды по расписанию.

## Расписание

`docker/cron/crontab`:

| Время | Команда | Описание |
|-------|---------|----------|
| 12:00 | `recurring-transaction/run` | Создание [[../modules/recurring|повторяющихся транзакций]] |
| 01:00 | `budget` | Автопродление [[../modules/budgets|бюджетов]] |

## Настройка

`config/console.php`:

```php
'container' => require __DIR__ . '/di.php',  // важно для DI
'controllerMap' => [
    'budget' => 'app\commands\BudgetRenewCommand',
    'recurring-transaction' => 'app\commands\RecurringTransactionController',
],
```

## Команды

### `recurring-transaction/run`

`commands/RecurringTransactionController.php::actionRun()`:
1. Найти все active recurring с `next_date <= today`
2. Для каждой:
   - Создать транзакцию через [[../services/TransactionService]] с префиксом "Авто:"
   - Сдвинуть `next_date` (`+P1D` / `+P7D` / `+P1M`)
   - Создать [[../modules/notifications|notification]] типа `reminder`

### `budget`

`commands/BudgetRenewCommand.php::actionIndex()`:
1. Найти бюджеты с `end_date < today`
2. Для каждого: создать новый бюджет на следующий период (start + 1 day)
3. Сбросить `spent = 0`
4. Вызвать `BudgetService::refreshSpentAmount()`

## Ручной запуск

```bash
docker-compose exec cron php /var/www/html/yii recurring-transaction/run
docker-compose exec cron php /var/www/html/yii budget
```

## Логи

- `runtime/logs/recurring.log`
- `runtime/logs/budget-renew.log`

## См. также

- [[../modules/recurring]]
- [[../modules/budgets]]
- [[../architecture/di-container]]
