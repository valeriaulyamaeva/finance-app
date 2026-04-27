# Повторяющиеся транзакции

Автоматическое создание транзакций по расписанию.

## Файлы

- `controllers/RecurringTransactionController.php` (Web)
- `commands/RecurringTransactionController.php` (Console — cron)
- `services/RecurringTransactionService.php`
- `models/RecurringTransaction.php`
- `models/forms/RecurringForm.php`

## Поля

`user_id`, `amount`, `currency`, `frequency` (daily/weekly/monthly), `next_date`, `category_id`, `budget_id`, `goal_id`, `description`, `active`

## Cron

См. [[../infrastructure/cron-jobs]]. Запускается ежедневно в 12:00:

```bash
php /var/www/html/yii recurring-transaction/run
```

Создаёт транзакции через [[../services/TransactionService]] с префиксом "Авто:" и сдвигает `next_date`.

## Уведомления

Каждая обработанная задача создаёт notification типа `reminder`.

## См. также

- [[transactions]], [[notifications]]
- [[../infrastructure/cron-jobs]]
