# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PastelFinance (Vale) — personal finance management web app. Yii 2 (PHP 8.3) backend with a Python FastAPI microservice for parsing bank statements (CSV/PDF). All services run in Docker Compose.

## Commands

### Start the project
```bash
docker-compose up -d --build
docker-compose exec php composer install --no-interaction --prefer-dist --ignore-platform-req=ext-http
docker-compose exec php php yii migrate --interactive=0
```

### Run tests (Codeception)
```bash
docker-compose exec php vendor/bin/codecept run            # all suites
docker-compose exec php vendor/bin/codecept run unit        # unit only
docker-compose exec php vendor/bin/codecept run functional  # functional only
docker-compose exec php vendor/bin/codecept run unit SomeTest  # single test
```

### Database migrations
```bash
docker-compose exec php php yii migrate                    # apply
docker-compose exec php php yii migrate/create <name>      # create new
docker-compose exec php php yii migrate/down 1             # rollback last
```

### Cron commands (manual run)
```bash
docker-compose exec cron php /var/www/html/yii recurring-transaction/run
docker-compose exec cron php /var/www/html/yii budget
```

### Rebuild parser microservice
```bash
docker-compose up -d --build parser
```

## Architecture

### Services (Docker Compose)
| Service | Container | Port | Purpose |
|---------|-----------|------|---------|
| nginx | finance_app_nginx | 8080 | Reverse proxy |
| php | finance_app_php | — | PHP-FPM app server |
| mysql | finance_app_mysql | 3307 | Database |
| parser | finance_app_parser | 8001 | Python bank statement parser |
| cron | finance_app_cron | — | Scheduled tasks (recurring transactions, budget renewal) |
| keycloak | finance_app_keycloak | 8180 | SSO authentication |
| keycloak_db | finance_app_keycloak_db | — | PostgreSQL for Keycloak |

### PHP Application Layer

**Service Layer pattern** (`services/`): Business logic lives in readonly service classes injected via Yii DI container (`config/di.php`). Controllers never touch the database directly — they delegate to services. `CurrencyService` is a singleton; everything else is per-request.

**DI wiring**: Defined in `config/di.php`, loaded in both `config/web.php` and `config/console.php`. Controllers receive services via constructor injection. Services that need other services also use constructor DI.

**Form Models** (`models/forms/`): Validation layer between controllers and services. Each CRUD operation validates through a form model before reaching the service.

**Custom Query Builders** (`models/queries/`): Scoped query methods like `forUser()`, `active()`, `forPeriod()` on ActiveRecord models.

**Key data flow**: Controller → Form (validate) → Service (business logic in DB transaction) → Model (persist)

### Transaction System
`TransactionService::create()` resolves the transaction type from the category, syncs budget spent amounts, updates goal progress, and fires notifications — all inside a single DB transaction.

### Bank Statement Import
Two-step flow: PHP `ImportController` receives file upload → sends to Python `parser` service via cURL (`http://parser:8000/parse/{csv|pdf}`) → returns parsed JSON → user previews in browser → confirms → `ImportService::bulkImport()` creates transactions via `TransactionService::create()`. Deduplication via SHA256 hashes stored in `imported_transaction_hash` table.

### Parser Microservice (`services/parser/`)
FastAPI app. `csv_parser.py` handles Windows-1251 encoded bank CSVs. `pdf_parser.py` uses pdfplumber for PDF table extraction. `categorizer.py` maps MCC codes to category suggestions. Supports multiple Belarusian bank formats with different column names (resolved via `COLUMN_ALIASES`).

### Authentication
Dual auth: traditional email/password (Yii native) + Keycloak OIDC via `yii2-authclient`. Keycloak realm `vale` auto-imports from `docker/keycloak/realm-export.json`. The `SiteController::onAuthSuccess()` callback handles user creation from OIDC tokens.

### Cron Automation
`docker/cron/crontab` runs two daily jobs:
- 12:00 — `recurring-transaction/run`: processes due recurring transactions, creates actual transactions, sends notifications
- 01:00 — `budget`: renews expired budget periods, resets spent counters

Console commands are in `commands/` and registered in `config/console.php` controllerMap.

### Frontend
Server-rendered PHP views with vanilla JS (no framework). Each page has its own CSS/JS in `web/css/` and `web/js/`. AJAX calls use `fetch()` with `X-CSRF-Token` header. Modals use Bootstrap 5. Config passed from PHP to JS via inline `<script>` blocks (e.g., `transactionConfig`, `importConfig`).

### Theming
Light/dark theme via CSS classes on `<body>` (`theme-light`/`theme-dark`). CSS variables defined in `views/layouts/_head.php`. User preference stored in `user.theme` column.

## Key Conventions

- PHP services are `readonly class` with constructor-injected dependencies
- All monetary operations wrapped in `Yii::$app->db->transaction()`
- Currency conversion goes through `CurrencyService::toBase()` (to BYN) then `fromBase()` (to target)
- Controllers return `Response::FORMAT_JSON` for AJAX endpoints
- Sidebar navigation is duplicated in each page view (not extracted to a shared partial for all pages)
- The `formName()` method on form models controls how `$form->load()` maps POST data — must match the JS field name prefix
