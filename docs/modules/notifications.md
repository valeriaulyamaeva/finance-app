# Уведомления

Алерты о превышении бюджета и достижении цели.

## Файлы

- `controllers/NotificationController.php`
- `services/NotificationService.php` → [[../services/NotificationService]]
- `models/Notification.php`
- `web/js/notifications.js`, `web/css/notifications.css`
- `views/layouts/main.php` (top-bar bell)

## Типы

| Тип | Триггер |
|-----|---------|
| `budget_exceed` | spent > budget.amount при создании транзакции |
| `goal_reached` | current_amount >= target_amount при вкладе в цель |
| `reminder` | recurring transaction обработан (cron) |
| `other` | прочее |

## Триггеры

Создаются в [[../services/TransactionService|TransactionService::syncRelatedEntities()]] и [[../infrastructure/cron-jobs|cron]].

## API

| Endpoint | Метод | Описание |
|----------|-------|----------|
| `/notification/index` | GET | список + unread_count |
| `/notification/mark-read?id=X` | POST | отметить прочитанным |
| `/notification/mark-all-read` | POST | отметить все |

## UI

Колокольчик в top-bar с красным бейджем. Dropdown с иконками по типу. Клик галочки → mark-read.

## См. также

- [[../services/NotificationService]]
- [[transactions]], [[budgets]], [[goals]]
