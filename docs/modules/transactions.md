# Транзакции

Учёт доходов и расходов с фильтрацией.

## Файлы

- `controllers/TransactionController.php`
- `services/TransactionService.php` → [[../services/TransactionService]]
- `models/Transaction.php`
- `models/forms/TransactionForm.php`
- `models/queries/TransactionQuery.php`
- `views/transaction/index.php`, `views/transaction/_modal.php`
- `web/js/transaction.js`, `web/css/transaction.css`

## Поля

`id`, `user_id`, `amount`, `currency`, `date`, `type` (income/expense/goal), `category_id`, `budget_id`, `goal_id`, `recurring_id`, `description`

## Тип транзакции

Резолвится из категории в [[../services/TransactionService|TransactionService]]:

```php
match ($category?->type) {
    Category::TYPE_INCOME => Transaction::TYPE_INCOME,
    Category::TYPE_GOAL => Transaction::TYPE_GOAL,
    default => Transaction::TYPE_EXPENSE,
};
```

## Синхронизация

При создании/обновлении/удалении:
- `budget.spent` пересчитывается → если > amount → [[notifications|уведомление]] `budget_exceed`
- `goal.current_amount` обновляется → если >= target → уведомление `goal_reached`
- Конвертация в валюту цели через [[../services/CurrencyService]]

## Фильтрация

`actionIndex()` принимает GET: `start`, `end`, `category_id`. UI — date inputs + dropdown.

## См. также

- [[budgets]], [[goals]], [[notifications]]
- [[../conventions/form-models]]
