# Стиль кода

## PHP

- **PHP 8.3+** features: `readonly`, union types, `match`, named args, constructor property promotion
- **Strict types**: `declare(strict_types=1)` в начале каждого файла
- **`final readonly class`** для сервисов — иммутабельность + конструкторная DI
- **Type hints обязательны** — параметры, возвращаемые типы, свойства
- **Namespaces**: `app\controllers`, `app\services`, `app\models`, `app\models\forms`, `app\models\queries`

## Контроллеры

- Тонкие — только парсинг запроса + вызов сервиса + JSON ответ
- AJAX-методы возвращают `array` с `Yii::$app->response->format = Response::FORMAT_JSON`
- Конструкторная DI сервисов

## Сервисы

```php
final readonly class FooService
{
    public function __construct(
        private OtherService $other,
    ) {}
}
```

## Транзакции БД

Денежные/каскадные операции:

```php
return Yii::$app->db->transaction(function () use ($form, $userId) {
    // ...
});
```

## JS

- **Vanilla JS** (без фреймворков)
- `fetch()` + `FormData` для AJAX
- CSRF: `X-CSRF-Token` header из `<meta>` тега
- Конфиг в JS через `$this->registerJs('const fooConfig = ...')` в view

## CSS

- Переменные темы в `views/layouts/_head.php`
- Каждая страница имеет свой CSS в `web/css/`
- Bootstrap 5 для модалок и базовых компонентов

## См. также

- [[form-models]]
- [[../architecture/service-layer]]
