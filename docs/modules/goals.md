# Цели накоплений

Целевые накопления с дедлайнами и прогрессом.

## Файлы

- `controllers/GoalController.php`
- `services/GoalService.php`
- `models/Goal.php`

## Поля

`user_id`, `name`, `target_amount`, `currency`, `current_amount`, `deadline`, `status` (active/completed/failed)

## Логика

При создании транзакции с `goal_id`:
- `current_amount += converted_amount` (см. [[../services/CurrencyService]])
- Если `current_amount >= target_amount` → notification `goal_reached`
- `updateStatus()` обновляет статус автоматически

## UI

`views/goal/index.php` — **эталон дизайна карточек** для всего приложения. Border-radius 24px, hover translateY(-10px).

См. [[../conventions/ui-patterns]].

## См. также

- [[transactions]], [[notifications]]
- [[../conventions/ui-patterns]]
