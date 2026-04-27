# Form Models

Валидация POST-данных перед передачей в сервис. **Не ActiveRecord** — отдельный слой.

**Расположение**: `models/forms/`

## Шаблон

```php
final class TransactionForm extends Model
{
    public mixed $amount = null;
    public mixed $currency = 'BYN';
    public mixed $date = null;
    public mixed $type = Transaction::TYPE_EXPENSE;

    public function formName(): string
    {
        return ''; // или 'User' если JS шлёт User[field]
    }

    public function rules(): array
    {
        return [
            [['amount', 'currency', 'date', 'type'], 'required'],
            [['amount'], 'number', 'min' => 0.01],
            // ...
        ];
    }
}
```

## Важно: formName

`formName()` управляет тем, как `$form->load($data)` маппит POST-данные.

| `formName()` | Ожидаемый POST |
|--------------|----------------|
| `''` (пусто) | `{amount: 100, ...}` |
| `'User'` | `{User: {username: ..., email: ...}}` |
| `'TransactionForm'` (default) | `{TransactionForm: {amount: ...}}` |

**Фронт должен совпадать!** JS:

```javascript
formData.append('User[username]', value);  // formName = 'User'
formData.append('amount', value);          // formName = ''
```

## Сценарии

```php
public function scenarios(): array
{
    $scenarios = parent::scenarios();
    $scenarios['login'] = ['email', 'password'];
    $scenarios['create'] = ['username', 'email', 'password', 'password_repeat'];
    return $scenarios;
}
```

Применение: `new User(['scenario' => 'login'])`.

## См. также

- [[../architecture/data-flow]]
- [[../modules/settings]] (UserProfileForm — formName='User')
