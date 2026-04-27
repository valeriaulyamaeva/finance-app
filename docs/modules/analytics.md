# Аналитика

Статистика, графики, сравнение периодов.

## Файлы

- `controllers/AnalyticsController.php`
- `views/analytics/index.php`
- `web/js/analytics.js` (Chart.js)
- `web/css/analytics.css`

## Графики

1. **Категории расходов** (doughnut)
2. **Месячные тренды** (line, 12 месяцев)
3. **Структура бюджета** (bar: доход/потрачено/остаток)
4. **Средний чек по категориям** (bar)
5. **Топ-5 категорий** (pie)
6. **Расходы по дням недели** (bar)

## Периоды

`?period=month|quarter|year|custom&start=...&end=...`

## Сравнение с предыдущим периодом

Дельта в % для income/expense, отображается бейджами `+/-X%` на summary cards.

## См. также

- [[transactions]], [[budgets]], [[categories]]
