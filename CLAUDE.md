# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Knowledge base

Detailed Obsidian-style documentation lives in `docs/`. **Always check `docs/` first** when working on a module — it has links to relevant files, conventions, and gotchas.

- `docs/README.md` — index (Map of Content)
- `docs/architecture/` — Service Layer, DI, data flow
- `docs/modules/` — per-feature docs (transactions, budgets, import, etc.)
- `docs/services/` — service classes with method signatures
- `docs/auth/` — Keycloak flows
- `docs/infrastructure/` — Docker, cron, DB schema, parser
- `docs/conventions/` — coding style, form models, UI patterns

Project-specific agents in `.claude/agents/`:
- `vale-architect` — for architectural questions
- `vale-debugger` — for known bugs/issues
- `vale-frontend` — for UI/CSS/JS work
- `vale-designer` — for theme/visual redesign work (use proactively when user complains about how something looks)

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
FastAPI app.
- `csv_parser.py` — Windows-1251 CSV with `;` separator
- `pdf_parser.py` — pdfplumber for table extraction
- `categorizer.py` — ~140 MCC codes + merchant keyword fallback. P2P split by direction (income/expense).
- `COLUMN_ALIASES` resolves variants of column names across different banks. Currency column is read separately from amount (important — USD transactions misread as BYN otherwise).

### Authentication (Keycloak as backend service)
Forms (`/login`, `/register`) are local Yii pages. Credentials are validated via Keycloak REST API:
- `KeycloakService::authenticate()` — `POST /realms/vale/protocol/openid-connect/token` (Resource Owner Password Grant)
- `KeycloakService::register()` — `POST /admin/realms/vale/users` via admin token

Passwords are stored **only in Keycloak** — local `password_hash` is a placeholder. On first successful login, local user is created and synced from Keycloak's JWT claims.

Realm config: `docker/keycloak/realm-export.json`. Important: `directAccessGrantsEnabled: true` on `vale-app` client; `VERIFY_PROFILE` required action disabled (otherwise password grant returns "Account is not fully set up"). `lastName` is set on user creation for the same reason.

### Cron Automation
`docker/cron/crontab` runs two daily jobs:
- 12:00 — `recurring-transaction/run`: processes due recurring transactions, creates actual transactions, sends notifications
- 01:00 — `budget`: renews expired budget periods, resets spent counters

Console commands are in `commands/` and registered in `config/console.php` controllerMap.

### Frontend
Server-rendered PHP views with vanilla JS (no framework). Each page has its own CSS/JS in `web/css/` and `web/js/`. AJAX calls use `fetch()` with `X-CSRF-Token` header. Modals use Bootstrap 5.

**Page config from PHP to JS** — pass through `window.*Config`, NOT `const *Config`:
```php
$this->registerJs('window.transactionConfig = ' . json_encode([...]) . ';', View::POS_HEAD);
```
`const`/`let` declarations don't attach to `window`, so external JS files would see them as `undefined`.

### UI helpers (global)
Loaded by `views/layouts/main.php` from `web/js/ui.js` and `web/css/ui.css`:
- `window.appConfirm({title, message, confirmText, danger})` — promise-based confirm modal (replaces native `confirm()`)
- `window.appToast(message, type)` — toast notification (`success`/`error`/`warning`/`info`)

### Theming
Single source of truth: `web/css/theme.css` with CSS variables on `:root` / `body.theme-light` / `body.theme-dark`. Loaded globally from `views/layouts/_head.php`.

Component CSS files (`transaction.css`, `budget.css`, etc.) **must use CSS variables** (`var(--bg-surface)`, `var(--text-primary)`, etc.), NOT hardcoded colors. Bootstrap defaults like `--bs-table-bg` are overridden in `theme.css`.

Dark theme is Linear/Vercel-inspired graphite (`#0b0b0f` → `#22222a` layered) with mint accent `#7dd3c7`. Borders carry hierarchy (`rgba(255,255,255,0.06)`), shadows are minimal. Active sidebar item gets a 3px mint indicator bar.

User preference stored in `user.theme` column. Applied via `theme-light` / `theme-dark` class on `<body>`.

## Key Conventions

- PHP services are `readonly class` with constructor-injected dependencies
- All monetary operations wrapped in `Yii::$app->db->transaction()`
- Currency conversion goes through `CurrencyService::toBase()` (to BYN) then `fromBase()` (to target)
- Controllers return `Response::FORMAT_JSON` for AJAX endpoints
- Sidebar navigation is duplicated in each page view (not extracted to a shared partial for all pages)
- The `formName()` method on form models controls how `$form->load()` maps POST data — must match the JS field name prefix
- All component CSS uses CSS variables from `theme.css`, never hardcoded colors
- Page configs go to JS via `window.*Config = ...`, not `const *Config = ...`
- Use `window.appConfirm()` and `window.appToast()` instead of native `confirm()`/`alert()`

## Performance

- OPcache enabled in `docker/php/Dockerfile` (192MB, validate_freq=2)
- `YII_DEBUG_TOOLBAR=0` env disables debug toolbar (huge perf win in dev)
- `CurrencyService` has 24h cache + fallback rates if external API is down
- Notification badge polls every 3 minutes (background only)
