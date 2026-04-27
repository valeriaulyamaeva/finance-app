# Service Layer

Бизнес-логика живёт в `services/`. Контроллеры — тонкие, только HTTP-обработка.

## Структура

```
services/
├── CategoryService.php        — категории
├── TransactionService.php     — транзакции (sync с budget/goal/notification)
├── BudgetService.php          — бюджеты + пересчёт spent
├── GoalService.php            — цели
├── RecurringTransactionService — повторяющиеся
├── CurrencyService.php        — конвертация валют (singleton)
├── ImportService.php          — оркестрация импорта выписок
├── NotificationService.php    — создание/чтение уведомлений
├── UserService.php            — auth, регистрация
└── KeycloakService.php        — REST API клиент Keycloak
```

## Конвенции

- **`readonly class`** с конструкторной DI
- **DI зависимости** — другие сервисы или `Connection $db`
- **`CurrencyService` — singleton** (один на всё приложение, кэширует курсы)

## Пример: TransactionService

```php
readonly class TransactionService
{
    public function __construct(
        private CurrencyService $currencyService,
        private NotificationService $notificationService,
    ) {}

    public function create(array $data, int $userId): Transaction {
        // 1. validate via TransactionForm
        // 2. resolve type by category
        // 3. wrap in db->transaction()
        // 4. sync budget.spent + goal.current_amount
        // 5. fire notification if budget exceeded / goal reached
    }
}
```

## Регистрация в DI

См. [[di-container]]. Все сервисы регистрируются в `config/di.php`.

## См. также

- [[../services/index]]
- [[di-container]]
- [[data-flow]]
