# Поток данных

```
Browser
  ↓ POST /transaction/create (JSON + CSRF)
Controller
  ↓ $request->post()
Form Model (validate)
  ↓ $form->attributes
Service (business logic, db transaction)
  ↓ ActiveRecord::save()
Database
```

## Этапы

### 1. Controller
Получает HTTP-запрос. **Не валидирует**, **не работает с БД**.

```php
public function actionCreate(): array
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    try {
        $tx = $this->service->create(Yii::$app->request->post(), Yii::$app->user->id);
        return ['success' => true, 'transaction' => $tx->toArray()];
    } catch (Throwable $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
```

### 2. Form Model
Чистая валидация, не ActiveRecord. Имеет `formName()` для load().

См. [[../conventions/form-models]].

### 3. Service
Бизнес-логика в `Yii::$app->db->transaction(function() { ... })`. Бросает `Exception` при ошибке.

### 4. Model (ActiveRecord)
Только persistence. Custom query builders в `models/queries/`.

## CSRF

JS отправляет `X-CSRF-Token` header. Yii проверяет автоматически.

```javascript
fetch(url, {
    method: 'POST',
    headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
});
```

## См. также

- [[service-layer]]
- [[../conventions/form-models]]
