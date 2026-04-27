# BudgetService

CRUD бюджетов + пересчёт `spent`.

**Файл**: `services/BudgetService.php`

## Зависимости

- `Connection $db`
- [[CurrencyService]]
- CategoryService

## Методы

- `create(int $userId, BudgetForm $form): Budget` — создание + `refreshSpentAmount`
- `update(int $id, int $userId, BudgetForm $form): Budget`
- `delete(int $id, int $userId): void`
- `refreshSpentAmount(Budget $budget): void` — полный пересчёт по транзакциям периода
- `findById(int $id, int $userId): Budget`
- `findActiveForCategory(int $categoryId, int $userId): ?Budget`

## Cron-интеграция

`BudgetRenewCommand` (commands/) использует `refreshSpentAmount` после создания нового бюджета на след. период. См. [[../infrastructure/cron-jobs]].

## См. также

- [[../modules/budgets]]
- [[TransactionService]]
