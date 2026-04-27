# UI Patterns

## Дизайн-система

Единый источник правды — **`web/css/theme.css`**. Все компоненты используют CSS-переменные, никакого хардкода цветов.

```css
.my-component {
    background: var(--bg-surface);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
}
```

## Темы

- `body.theme-light` — пастельная мятная (фон `#f6f5f1`, accent `#a3c9c9`)
- `body.theme-dark` — Linear-inspired графит (фон `#0b0b0f` → surfaces `#131318` → `#22222a`, accent `#7dd3c7`)

Переключение через `web/js/settings.js::applyTheme()`. Сохранение в `user.theme` колонке.

## Карточки — эталон (Goal)

```css
.card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);  /* 24px */
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-6px);
    border-color: var(--border-color-strong);
    box-shadow: var(--shadow-lg);
}
```

Применено везде: budget, category, transaction, analytics, goal.

## Кнопки

```css
.btn-add {              /* primary action */
    background: var(--color-primary);
    color: var(--color-primary-contrast);
    border-radius: var(--border-radius-pill);
}

.btn-cancel {           /* secondary */
    background: var(--color-secondary);
    color: var(--text-primary);
}
```

В тёмной — primary с subtle gradient + inner highlight + glow на hover.

## Сайдбар

Дублируется в каждом view (не отдельный partial). Все ссылки одинаковые. Активный пункт получает `class="active"`:

```html
<li><a href="/transaction" class="active">Транзакции</a></li>
```

В тёмной теме активный пункт — 3px мятная полоска слева + soft mint фон.

## Модалки

Bootstrap 5 modals. **Не использовать `bg-light`, `bg-white`** — они ломают тёмную тему. Если нужен другой фон, через CSS-переменные:

```html
<div class="modal-footer border-0 p-3">  <!-- ✓ -->
<div class="modal-footer bg-light p-3">  <!-- ✗ -->
```

## Глобальные UI helpers

Подключены через `views/layouts/main.php` (`web/js/ui.js` + `web/css/ui.css`):

### `window.appConfirm()` — confirm modal

```javascript
const ok = await window.appConfirm({
    title: 'Удалить транзакцию?',
    message: 'Это действие нельзя отменить.',
    confirmText: 'Удалить',
    cancelText: 'Отмена',
    danger: true,  // красная иконка + красная кнопка
});
if (!ok) return;
// удаляем
```

Возвращает `Promise<boolean>`. **Не использовать native `confirm()`** — он не стилизуется и блокирует UI.

### `window.appToast()` — toast

```javascript
window.appToast('Сохранено', 'success');
window.appToast('Ошибка соединения', 'error');
window.appToast('Внимание', 'warning');
window.appToast('Подсказка', 'info', 5000);  // 5s
```

Появляется в правом верхнем углу, авто-исчезает (3.5s).

## AJAX паттерн

```javascript
fetch(url, {
    method: 'POST',
    headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
    body: formData,
})
.then(r => r.json())
.then(data => {
    if (data.success) {
        window.appToast('OK', 'success');
        location.reload();
    } else {
        window.appToast(data.message, 'error');
    }
});
```

## Page config (PHP → JS)

**Используй `window.*Config`, НЕ `const *Config`** — `const` объявления через `registerJs` не попадают в global scope для других JS-файлов:

```php
// ✓ Правильно
$this->registerJs('window.transactionConfig = ' . json_encode([...]) . ';', View::POS_HEAD);

// ✗ Неправильно — внешний transaction.js увидит undefined
$this->registerJs('const transactionConfig = ' . json_encode([...]) . ';', View::POS_HEAD);
```

В JS:
```javascript
const config = window.transactionConfig || { urls: {} };
```

## Подсветка пустых полей

Транзакции и превью импорта без выбранной категории подсвечиваются warning-фоном с бейджем. См. `.card--no-category` в `transaction.css`.

## Цветовая семантика

- **Income** — `var(--color-success)` зелёный
- **Expense** — `var(--color-danger)` красный
- **Goal type badge** — `var(--color-info)` синий
- **Period badge** — `var(--color-info-soft)` фон + `var(--color-info-text)` текст

## См. также

- [[coding-style]]
- [[../modules/goals]] (эталон карточек)
- [[form-models]]
