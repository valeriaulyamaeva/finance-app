# TransactionService

Создание/обновление/удаление транзакций с синхронизацией бюджетов, целей и уведомлений.

**Файл**: `services/TransactionService.php`

## Зависимости

- [[CurrencyService]] — конвертация валют
- [[NotificationService]] — создание уведомлений

## Методы

### `create(array $data, int $userId): Transaction`

1. Валидирует через `TransactionForm`
2. Резолвит `type` из категории
3. Открывает DB transaction
4. Сохраняет транзакцию
5. Вызывает `syncRelatedEntities()`

### `update(int $id, array $data): Transaction`

Откатывает старые изменения через `revertRelatedEntities()`, потом применяет новые.

### `delete(int $id): void`

Откатывает влияние на бюджет/цель.

### `syncRelatedEntities(Transaction $t)`

- Если есть `category_id` и type=expense/goal → ищет бюджет в дате транзакции, увеличивает `spent`, может создать notification `budget_exceed`
- Если есть `goal_id` → конвертирует сумму в валюту цели, увеличивает `current_amount`, может создать notification `goal_reached`

### `getMonthlySummary(int $userId, ?string $start, ?string $end): array`

Агрегирует prevBalance, income, expense, balance в валюте пользователя.

## См. также

- [[../modules/transactions]]
- [[../modules/notifications]]
- [[CurrencyService]]
