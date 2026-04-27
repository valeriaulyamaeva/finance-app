# Database Schema

MySQL 8.0, charset utf8mb4. Миграции в `migrations/`.

## Таблицы

### `user`
Пользователи. Пароль — заглушка (Keycloak хранит реальный).

```
id, username, email, password_hash, auth_key, access_token,
theme (light/dark), currency (BYN/USD/EUR/RUB),
avatar, last_login, status, created_at, updated_at
```

### `category`
```
id, user_id (FK), name, type (income/expense/goal), created_at, updated_at
```

### `transaction`
```
id, user_id (FK), amount, currency, date,
type (income/expense/goal),
category_id (FK), budget_id (FK), goal_id (FK), recurring_id (FK),
description, created_at, updated_at
```

### `budget`
```
id, user_id (FK), category_id (FK), name,
amount, currency, period (daily/weekly/monthly/yearly),
start_date, end_date, spent,
created_at, updated_at
```

### `goal`
```
id, user_id (FK), name, target_amount, currency,
current_amount, deadline, status (active/completed/failed),
created_at, updated_at
```

### `recurring_transaction`
```
id, user_id (FK), amount, currency,
frequency (daily/weekly/monthly), next_date,
category_id (FK), budget_id (FK), goal_id (FK),
description, active, created_at, updated_at
```

### `notification`
```
id, user_id (FK), message, type (enum),
related_type, related_id, read_status, created_at, updated_at
```

### `statement_import`
История импорта банковских выписок.
```
id, user_id (FK), filename, file_type (csv/pdf),
transactions_count, created_at
```

### `imported_transaction_hash`
Дедупликация импорта.
```
id, user_id (FK), hash (SHA256, unique with user_id),
transaction_id (FK), import_id (FK), created_at
```

## Связи (ER)

```
user 1─┬─N category
       ├─N transaction ──┬─ category
       ├─N budget         ├─ budget
       ├─N goal           ├─ goal
       ├─N recurring_transaction ──── recurring_transaction
       ├─N notification
       └─N statement_import ─N─ imported_transaction_hash
```

## См. также

- [[../modules/index]]
