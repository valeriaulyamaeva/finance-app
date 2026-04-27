# Docker Services

Конфиг: `docker-compose.yml`.

## Сервисы

| Сервис | Контейнер | Порт | Образ | Назначение |
|--------|-----------|------|-------|------------|
| nginx | finance_app_nginx | 8080 | nginx:latest | Reverse proxy |
| php | finance_app_php | — | php:8.3-fpm (custom) | Yii приложение |
| mysql | finance_app_mysql | 3307 | mysql:8.0 | Основная БД |
| parser | finance_app_parser | 8001 | python:3.12-slim (custom) | [[parser-microservice]] |
| cron | finance_app_cron | — | php:8.3-cli (custom) | [[cron-jobs]] |
| keycloak | finance_app_keycloak | 8180 | quay.io/keycloak/keycloak:26.0 | [[../auth/keycloak]] |
| keycloak_db | finance_app_keycloak_db | — | postgres:16-alpine | БД для Keycloak |

## Volumes

- `mysql_data` — данные MySQL
- `keycloak_db_data` — данные PostgreSQL
- `.:/var/www/html` — проект монтируется в php/cron (live reload)

## Внутренняя сеть

Все сервисы общаются по именам:
- PHP → MySQL: `mysql:3306`
- PHP → Parser: `http://parser:8000`
- PHP → Keycloak: `http://keycloak:8080`

## Dockerfiles

- `docker/php/Dockerfile` — PHP-FPM с расширениями (gd, pdo, pdo_mysql, curl) + composer
- `docker/cron/Dockerfile` — PHP-CLI + cron daemon, копирует `docker/cron/crontab`
- `services/parser/Dockerfile` — Python + uvicorn

## Запуск

```bash
docker-compose up -d --build
```

## См. также

- [[cron-jobs]]
- [[parser-microservice]]
- [[../auth/keycloak]]
