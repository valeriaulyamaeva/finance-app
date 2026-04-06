# PastelFinance (Vale)

Веб-приложение для управления личными финансами. Yii 2 + Python-микросервис для парсинга банковских выписок.

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

Приложение доступно по адресу: **http://localhost:8080**

## Сервисы

| Сервис | Контейнер | Порт |
|--------|-----------|------|
| Приложение (Nginx) | finance_app_nginx | 8080 |
| PHP-FPM | finance_app_php | - |
| MySQL 8.0 | finance_app_mysql | 3307 |
| Парсер выписок (FastAPI) | finance_app_parser | 8001 |
| Cron | finance_app_cron | - |

## Основные возможности

- Учёт доходов и расходов
- Бюджеты по категориям (день/неделя/месяц/год)
- Цели накоплений с отслеживанием прогресса
- Повторяющиеся транзакции (автоматическое создание по расписанию)
- Импорт банковских выписок (CSV, PDF) с автокатегоризацией по MCC-кодам
- Аналитика и статистика
- Мультивалютность (BYN, USD, EUR, RUB)
- Тёмная/светлая тема
- Google OAuth авторизация

## Стек

- **Backend**: PHP 8.3, Yii 2
- **Frontend**: Bootstrap 5, vanilla JS
- **БД**: MySQL 8.0
- **Парсер**: Python 3.12, FastAPI, pdfplumber
- **Деплой**: Docker Compose
