# Категории

Классификация транзакций по типам income/expense/goal.

## Файлы

- `controllers/CategoryController.php`
- `services/CategoryService.php`
- `models/Category.php`
- `models/queries/CategoryQuery.php`
- `views/category/index.php`

## Типы

```php
const TYPE_INCOME = 'income';
const TYPE_EXPENSE = 'expense';
const TYPE_GOAL = 'goal';
```

Тип категории определяет тип создаваемой [[transactions|транзакции]].

## Дефолтные категории

Создаются при регистрации через `CategoryService::createDefaultCategories(int $userId)`.

## Связи

- → [[transactions]] (1:N)
- → [[budgets]] (1:N через `category_id`)
- → [[recurring]] (1:N)

## См. также

- [[transactions]], [[budgets]]
