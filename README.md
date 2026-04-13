# PastelFinance (Vale)

Веб-приложение для управления личными финансами. Yii 2 (PHP 8.3) + Python-микросервис для парсинга банковских выписок + Keycloak для аутентификации.

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

Приложение: **http://localhost:8080**
Keycloak Admin Console: **http://localhost:8180** (admin / admin)

> Keycloak стартует ~30 секунд. Подождите перед первой авторизацией.

## Сервисы

| Сервис | Контейнер | Порт | Назначение |
|--------|-----------|------|------------|
| Nginx | finance_app_nginx | 8080 | Reverse proxy |
| PHP-FPM | finance_app_php | - | Сервер приложения |
| MySQL 8.0 | finance_app_mysql | 3307 | База данных |
| Parser | finance_app_parser | 8001 | Парсер банковских выписок (FastAPI) |
| Cron | finance_app_cron | - | Автоматические задачи |
| Keycloak | finance_app_keycloak | 8180 | SSO-аутентификация |
| Keycloak DB | finance_app_keycloak_db | - | PostgreSQL для Keycloak |

## Основные возможности

- **Транзакции** — учёт доходов и расходов с фильтрацией по датам и категориям
- **Бюджеты** — лимиты расходов по категориям (день/неделя/месяц/год) с автопродлением
- **Цели** — накопления с отслеживанием прогресса
- **Повторяющиеся платежи** — автоматическое создание транзакций по расписанию (cron)
- **Импорт выписок** — загрузка CSV/PDF из банков, автокатегоризация по MCC-кодам, превью перед импортом
- **Аналитика** — графики расходов по категориям, месяцам, дням недели; выбор периода; сравнение с предыдущим периодом
- **Уведомления** — автоматические алерты при превышении бюджета и достижении цели
- **Мультивалютность** — BYN, USD, EUR, RUB с конвертацией
- **Тёмная/светлая тема**
- **Keycloak** — аутентификация и регистрация через Keycloak (пароли хранятся только в Keycloak)

## Стек

- **Backend**: PHP 8.3, Yii 2
- **Frontend**: Bootstrap 5, Chart.js, vanilla JS
- **БД**: MySQL 8.0
- **Парсер**: Python 3.12, FastAPI, pdfplumber
- **Auth**: Keycloak 26 (OIDC, Resource Owner Password Grant)
- **Деплой**: Docker Compose

## Тесты

```bash
docker-compose exec php vendor/bin/codecept run            # все
docker-compose exec php vendor/bin/codecept run unit        # unit
docker-compose exec php vendor/bin/codecept run functional  # functional
```

## Cron-задачи

Выполняются автоматически в контейнере `cron`:

| Время | Команда | Назначение |
|-------|---------|------------|
| 12:00 | `recurring-transaction/run` | Генерация повторяющихся транзакций |
| 01:00 | `budget` | Автопродление истёкших бюджетов |

Ручной запуск:
```bash
docker-compose exec cron php /var/www/html/yii recurring-transaction/run
docker-compose exec cron php /var/www/html/yii budget
```

## Миграции

```bash
docker-compose exec php php yii migrate                    # применить
docker-compose exec php php yii migrate/create <name>      # создать новую
docker-compose exec php php yii migrate/down 1             # откатить последнюю
```
