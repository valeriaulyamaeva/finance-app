# PastelFinance (Vale)

Веб-приложение для управления личными финансами. Yii 2 (PHP 8.3) + Python-микросервис парсера выписок + Keycloak для аутентификации.

## Требования

- [Docker](https://www.docker.com/get-started/) и Docker Compose

## Запуск

```bash
git clone <repo-url> vale
cd vale

# Поднять все контейнеры
docker-compose up -d --build

# Установить PHP-зависимости
docker-compose exec php composer install --no-interaction --prefer-dist --ignore-platform-req=ext-http

# Применить миграции
docker-compose exec php php yii migrate --interactive=0
```

- Приложение: **http://localhost:8080**
- Keycloak Admin Console: **http://localhost:8180** (admin / admin)

> Keycloak стартует ~30 секунд. Подождите перед первой авторизацией.

## Сервисы

| Сервис | Контейнер | Порт | Назначение |
|--------|-----------|------|------------|
| Nginx | finance_app_nginx | 8080 | Reverse proxy |
| PHP-FPM | finance_app_php | — | Сервер приложения (с OPcache) |
| MySQL 8.0 | finance_app_mysql | 3307 | База данных |
| Parser | finance_app_parser | 8001 | Парсер банковских выписок (FastAPI) |
| Cron | finance_app_cron | — | Автозадачи (recurring/budget) |
| Keycloak | finance_app_keycloak | 8180 | SSO-аутентификация |
| Keycloak DB | finance_app_keycloak_db | — | PostgreSQL для Keycloak |

## Возможности

### Финансы
- **Транзакции** — доходы и расходы с фильтрацией по датам и категориям
- **Бюджеты** — лимиты по категориям (день/неделя/месяц/год) с автопродлением через cron
- **Цели** — накопления с прогрессом и дедлайнами
- **Повторяющиеся платежи** — автоматическое создание транзакций по расписанию
- **Уведомления** — алерты при превышении бюджета и достижении цели

### Импорт выписок
- Поддержка CSV (Windows-1251) и PDF выписок белорусских банков
- Несколько форматов выписок (мини-выписка, расширенная, разные банки)
- Автоматическое определение валюты (BYN/USD/EUR/RUB) — отдельная колонка в выписке
- Автокатегоризация по **MCC-кодам** (~140 кодов) + ключевым словам мерчантов
- Дедупликация через SHA256-хеши
- Превью перед импортом, подсветка транзакций без категории

### Категории (29 дефолтных)
**Доходы**: Зарплата, Подработка, Кэшбек, Возврат, Переводы (доход)
**Расходы**: Продукты, Еда, Рестораны, Алкоголь, Транспорт, Топливо, Авто, Жилье, Коммунальные, Связь, Дом, Электроника, Подписки, Автосписания, Развлечения, Здоровье, Красота, Спорт, Одежда, Хобби, Питомцы, Путешествия, Образование, Маркетплейс, Благотворительность, Переводы (расход), Прочее

### Аналитика
- 6 графиков (Chart.js): расходы по категориям, тренды по месяцам, по дням недели, топ-5 категорий и др.
- Выбор периода: месяц / квартал / год / произвольный
- Сравнение с предыдущим периодом

### Авторизация
- **Keycloak** как backend-аутентификация (формы наши, проверка через Keycloak REST API)
- Resource Owner Password Grant для логина
- Admin REST API для регистрации
- Пароли хранятся **только в Keycloak**, в локальной БД — заглушка
- Автосинхронизация пользователя при первом логине

### UX
- Светлая (мятная пастель) и тёмная (Linear-inspired графит) темы
- Современная UI: confirm-модалки, тосты, layered surfaces
- Адаптив (mobile-first)
- Мультивалютность (BYN/USD/EUR/RUB) с автоконвертацией

## Стек

- **Backend**: PHP 8.3, Yii 2.0
- **Frontend**: Bootstrap 5, Chart.js, vanilla JS, FontAwesome 6
- **БД**: MySQL 8.0
- **Парсер**: Python 3.12, FastAPI, pdfplumber
- **Auth**: Keycloak 26 (OIDC, Resource Owner Password Grant)
- **Деплой**: Docker Compose

## Команды

### Тесты (Codeception)
```bash
docker-compose exec php vendor/bin/codecept run            # все
docker-compose exec php vendor/bin/codecept run unit        # unit
docker-compose exec php vendor/bin/codecept run functional  # functional
```

### Миграции
```bash
docker-compose exec php php yii migrate                    # применить
docker-compose exec php php yii migrate/create <name>      # создать
docker-compose exec php php yii migrate/down 1             # откатить
```

### Cron-задачи (ручной запуск)
```bash
docker-compose exec cron php /var/www/html/yii recurring-transaction/run
docker-compose exec cron php /var/www/html/yii budget
```

Расписание (`docker/cron/crontab`):
- **12:00** ежедневно — генерация повторяющихся транзакций
- **01:00** ежедневно — автопродление истёкших бюджетов

### Пересборка парсера
```bash
docker-compose up -d --build parser
```

## Документация

Полная база знаний (Obsidian-vault) в `docs/`:
- `docs/README.md` — index (Map of Content)
- `docs/architecture/` — Service Layer, DI, потоки данных
- `docs/modules/` — описание каждого модуля
- `docs/services/` — методы и зависимости сервисов
- `docs/auth/` — Keycloak flows
- `docs/infrastructure/` — Docker, cron, БД, парсер
- `docs/conventions/` — стиль кода, form models, UI patterns

## Структура проекта

```
controllers/          — Yii контроллеры
services/             — бизнес-логика (Service Layer)
services/parser/      — Python FastAPI микросервис парсера
models/               — ActiveRecord
models/forms/         — form models для валидации
models/queries/       — кастомные query builders
views/                — PHP-шаблоны
web/css/              — стили (theme.css = единая дизайн-система)
web/js/               — vanilla JS
migrations/           — миграции БД
commands/             — Yii console-команды (cron)
docker/               — Dockerfiles
docs/                 — Obsidian база знаний
.claude/agents/       — project-specific Claude агенты
```
