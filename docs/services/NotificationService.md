# NotificationService

CRUD уведомлений.

**Файл**: `services/NotificationService.php`

## Методы

- `createNotification(int $userId, string $message, string $type, ?string $relatedType, ?int $relatedId): ?Notification`
- `getUserNotifications(int $userId, bool $onlyUnread = false): array`
- `countUnread(int $userId): int`
- `markAsRead(int $notificationId): bool`
- `deleteNotification(int $notificationId): bool`

## Где вызывается

- [[TransactionService]] — `budget_exceed`, `goal_reached`
- `commands/RecurringTransactionController` (cron) — `reminder`

## Структура `notification`

```
id, user_id, message, type (enum), related_type, related_id, read_status, created_at, updated_at
```

См. [[../infrastructure/database-schema]].

## См. также

- [[../modules/notifications]]
- [[TransactionService]]
