# Архитектура — Обзор

Vale — Yii 2 (PHP 8.3) монолит + Python-микросервис парсера + Keycloak SSO. Всё в Docker Compose.

## Высокоуровневая схема

```
┌─────────┐     ┌──────────────────────────────┐
│ Browser │────▶│ Nginx :8080                  │
└─────────┘     │   ↓                          │
                │ PHP-FPM (Yii 2)              │
                │   ├── Controllers            │
                │   ├── Services (DI)          │
                │   └── Models (ActiveRecord)  │
                │   ↓                          │
                │ MySQL :3307                  │
                └──────┬───────────────────────┘
                       │
       ┌───────────────┼─────────────────┐
       ↓               ↓                 ↓
   ┌────────┐     ┌──────────┐      ┌──────────┐
   │ Parser │     │ Cron     │      │ Keycloak │
   │ :8001  │     │ daily    │      │ :8180    │
   └────────┘     └──────────┘      └──────────┘
```

## Ключевые принципы

1. **[[service-layer|Service Layer]]** — вся бизнес-логика в `services/`, не в контроллерах
2. **[[di-container|DI через Yii Container]]** — конструкторная инъекция
3. **[[data-flow|Form Models]]** — валидация POST до сервисов
4. **DB Transactions** — все денежные операции в `Yii::$app->db->transaction()`
5. **JSON API для AJAX** — контроллеры возвращают JSON для модалок

## Основные потоки

- **Создание транзакции** → [[../modules/transactions]] → синхронизация бюджетов/целей → [[../modules/notifications]]
- **Импорт выписки** → [[../modules/import]] → Python парсер → превью → bulk import
- **Cron** → [[../infrastructure/cron-jobs]] → recurring + budget renew

## См. также

- [[../modules/index]]
- [[../services/index]]
- [[../infrastructure/docker-services]]
