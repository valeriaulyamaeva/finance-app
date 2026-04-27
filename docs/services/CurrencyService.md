# CurrencyService

Конвертация валют (BYN, USD, EUR, RUB).

**Файл**: `services/CurrencyService.php`

## Singleton

Зарегистрирован как singleton в `config/di.php` — один инстанс на всё приложение, кэширует курсы.

## Базовая валюта

**BYN** — все курсы относительно неё.

## Методы

- `toBase(float $amount, string $from): float` — конвертация в BYN
- `fromBase(float $amount, string $to): float` — конвертация из BYN
- `convert(float $amount, string $from, string $to): float` — прямая конвертация
- `getRate(string $currency): float` — курс к BYN

## Кэш

`yii\caching\FileCache` с TTL 3600 секунд. Внешний API — exchangerate-api.com.

## Использование

```php
// Конвертировать 100 USD в EUR
$eur = $this->currencyService->convert(100, 'USD', 'EUR');

// Через base
$byn = $this->currencyService->toBase(100, 'USD');
$rub = $this->currencyService->fromBase($byn, 'RUB');
```

## См. также

- [[TransactionService]]
- [[BudgetService]]
