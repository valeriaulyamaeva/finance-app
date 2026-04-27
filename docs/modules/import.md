# Импорт банковских выписок

Загрузка CSV/PDF выписок белорусских банков с автокатегоризацией.

## Архитектура

```
[Browser] → загрузка CSV/PDF → [Yii ImportController]
                                      ↓ cURL
                              [Python Parser :8001]
                                      ↓ парсинг + MCC
                              JSON ответ
                                      ↓
                              превью в браузере
                                      ↓ confirm
                              [TransactionService::create()]
```

## Файлы

### Backend
- `controllers/ImportController.php`
- `services/ImportService.php` → [[../services/ImportService]]
- `services/parser/` — Python FastAPI ([[../infrastructure/parser-microservice]])
- `models/StatementImport.php`
- `models/ImportedTransactionHash.php`

### Frontend
- `views/import/index.php`
- `web/js/import.js`, `web/css/import.css`

## Поддерживаемые форматы

- **CSV** — Windows-1251, разделитель `;`, белорусские банки
- **PDF** — pdfplumber, два формата (мини-выписка и расширенная)

## Дедупликация

SHA256 хеш `(date|amount|description)` хранится в `imported_transaction_hash`. Уникальность по `(user_id, hash)`.

## Категоризация

По MCC-коду (см. `services/parser/app/categorizer.py`):
- 4121 → Транспорт
- 4814 → Связь
- 5411 → Продукты
- ...

## Endpoints

| URL | Метод | Назначение |
|-----|-------|------------|
| `/import` | GET | страница загрузки |
| `/import/parse` | POST | загрузка файла → превью |
| `/import/confirm` | POST | bulk создание транзакций |

## См. также

- [[../infrastructure/parser-microservice]]
- [[../services/ImportService]]
- [[transactions]]
