# Parser Microservice

Python FastAPI сервис для парсинга банковских выписок (CSV/PDF).

**Путь**: `services/parser/`

## Запуск

```yaml
parser:
  build: ./services/parser
  ports: ["8001:8000"]
```

Внутренний URL: `http://parser:8000` (Docker DNS).

## Структура

```
services/parser/
├── Dockerfile          # python:3.12-slim
├── requirements.txt    # fastapi, uvicorn, pdfplumber
└── app/
    ├── main.py            # FastAPI endpoints
    ├── schemas.py         # Pydantic models
    ├── categorizer.py     # MCC → категория
    └── parsers/
        ├── csv_parser.py  # Windows-1251, разделитель ;
        └── pdf_parser.py  # pdfplumber tables
```

## Endpoints

| URL | Метод | Назначение |
|-----|-------|------------|
| `/health` | GET | Проверка живости |
| `/parse/csv` | POST | Парсит CSV (multipart/form-data) |
| `/parse/pdf` | POST | Парсит PDF |

## Ответ

```json
{
  "success": true,
  "transactions": [
    {
      "date": "2026-03-30",
      "amount": 14.50,
      "currency": "BYN",
      "type": "expense",
      "description": "MOBIL. PRIL. YANDEX GO MINSK",
      "mcc": "4121",
      "operation_type": "Покупка",
      "suggested_category": "Транспорт",
      "hash": "sha256(...)"
    }
  ],
  "errors": [],
  "metadata": {"currency": "BYN"}
}
```

## Поддерживаемые форматы

CSV/PDF от двух разных белорусских банков. Колонки резолвятся через `COLUMN_ALIASES` в `csv_parser.py`.

## MCC-категоризация

`categorizer.py` маппит MCC-коды → русские названия категорий. Также маппит operation_type ("Зачисление зарплаты" → "Зарплата").

## См. также

- [[../modules/import]]
- [[../services/ImportService]]
