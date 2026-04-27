# ImportService

Оркестрация импорта банковских выписок.

**Файл**: `services/ImportService.php`

## Зависимости

- [[TransactionService]]

## Методы

### `parseFile(string $filePath, string $fileType): array`

Отправляет файл в Python-парсер через cURL:
```
POST http://parser:8000/parse/{csv|pdf}
```

Возвращает массив с распарсенными транзакциями.

### `getExistingHashes(int $userId, array $hashes): array`

Возвращает хеши которые уже импортированы (из `imported_transaction_hash`).

### `mapCategories(int $userId, array $parsedTransactions): array`

Маппит `suggested_category` (например "Транспорт") на реальный `category_id` пользователя.

### `bulkImport(int $userId, array $transactions, string $filename, string $fileType): StatementImport`

В DB transaction:
1. Создаёт запись `StatementImport`
2. Для каждой транзакции (если category_id есть и хеш не дубликат):
   - Создаёт через [[TransactionService]]
   - Сохраняет хеш в `imported_transaction_hash`
3. Обновляет `transactions_count`

## Конфиг

URL парсера: `Yii::$app->params['parserServiceUrl']` = `http://parser:8000`

## См. также

- [[../modules/import]]
- [[../infrastructure/parser-microservice]]
