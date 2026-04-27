# 📘 Vale — База знаний

> Obsidian-vault для проекта PastelFinance (Vale). Открой в Obsidian для графа связей.

## Структура

### 🏗 [[architecture/overview|Архитектура]]
- [[architecture/overview]] — общий обзор системы
- [[architecture/service-layer]] — паттерн Service Layer
- [[architecture/di-container]] — DI контейнер Yii
- [[architecture/data-flow]] — поток данных Controller → Form → Service → Model

### 🧩 [[modules/index|Модули]]
- [[modules/transactions]] — транзакции
- [[modules/budgets]] — бюджеты
- [[modules/categories]] — категории
- [[modules/goals]] — цели
- [[modules/recurring]] — повторяющиеся платежи
- [[modules/analytics]] — аналитика
- [[modules/notifications]] — уведомления
- [[modules/import]] — импорт банковских выписок
- [[modules/settings]] — настройки пользователя

### ⚙️ [[services/index|Сервисы]]
- [[services/TransactionService]]
- [[services/BudgetService]]
- [[services/ImportService]]
- [[services/KeycloakService]]
- [[services/CurrencyService]]
- [[services/NotificationService]]
- [[services/CategoryService]]

### 🔐 [[auth/index|Авторизация]]
- [[auth/keycloak]] — настройка Keycloak
- [[auth/login-flow]] — flow логина
- [[auth/registration-flow]] — flow регистрации

### 🐳 [[infrastructure/index|Инфраструктура]]
- [[infrastructure/docker-services]] — Docker Compose сервисы
- [[infrastructure/cron-jobs]] — cron-задачи
- [[infrastructure/database-schema]] — схема БД
- [[infrastructure/parser-microservice]] — Python парсер

### 📐 [[conventions/index|Конвенции]]
- [[conventions/coding-style]] — стиль кода
- [[conventions/form-models]] — паттерн form models
- [[conventions/ui-patterns]] — UI паттерны

## Быстрый старт

```bash
docker-compose up -d --build
docker-compose exec php composer install --no-interaction --prefer-dist --ignore-platform-req=ext-http
docker-compose exec php php yii migrate --interactive=0
```

- Приложение: http://localhost:8080
- Keycloak: http://localhost:8180 (admin/admin)

## Project-specific Claude агенты

В `.claude/agents/`:
- **vale-architect** — архитектурные вопросы, дизайн фичей
- **vale-debugger** — известные баги (settings, notifications, cron, Keycloak, parser, theme)
- **vale-frontend** — UI/CSS/JS работы
- **vale-designer** — редизайн темы и визуальной составляющей (использовать когда жалоба на внешний вид)

Используй через Agent tool с `subagent_type: vale-*`.
