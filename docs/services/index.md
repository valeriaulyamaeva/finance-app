# Сервисы

Бизнес-логика приложения. См. [[../architecture/service-layer]].

## Список

- [[TransactionService]] — создание/обновление/удаление транзакций, sync с budget/goal/notification
- [[BudgetService]] — бюджеты, пересчёт spent
- [[ImportService]] — оркестрация импорта выписок (вызов Python parser)
- [[KeycloakService]] — REST-клиент Keycloak (auth + register)
- [[CurrencyService]] — конвертация валют (singleton, кэш курсов)
- [[NotificationService]] — CRUD уведомлений
- CategoryService — категории + дефолтные
- GoalService — цели + прогресс
- RecurringTransactionService — повторяющиеся транзакции
- UserService — регистрация, профиль

## DI

Все зарегистрированы в `config/di.php`. См. [[../architecture/di-container]].
