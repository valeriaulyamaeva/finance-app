# Бюджеты

Лимиты расходов по категориям с автопродлением.

## Файлы

- `controllers/BudgetController.php`
- `services/BudgetService.php` → [[../services/BudgetService]]
- `models/Budget.php`
- `commands/BudgetRenewCommand.php` (cron)

## Период

ENUM: `daily`, `weekly`, `monthly`, `yearly`. Использовался для [[../infrastructure/cron-jobs|автопродления]].

## Поля

`user_id`, `category_id`, `name`, `amount`, `currency`, `period`, `start_date`, `end_date`, `spent`

## Логика

- При создании транзакции в категории бюджета → `spent += amount` (см. [[transactions]])
- `BudgetService::refreshSpentAmount()` — полный пересчёт по всем транзакциям периода
- `BudgetRenewCommand` (cron 01:00) — создаёт новый бюджет на след. период когда текущий истёк

## Уведомления

При `spent > amount` → [[notifications|notification]] `budget_exceed`

## См. также

- [[transactions]], [[notifications]]
- [[../infrastructure/cron-jobs]]
