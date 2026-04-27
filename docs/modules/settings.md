# Настройки пользователя

Профиль, тема, валюта.

## Файлы

- `controllers/SettingsController.php`
- `services/UserService.php` (метод `updateProfile`)
- `models/forms/UserProfileForm.php`
- `views/settings/index.php`
- `web/js/settings.js`

## Важно: formName

`UserProfileForm::formName()` возвращает `'User'`. JS отправляет `User[username]`, `User[email]`, `User[theme]`, `User[currency]`, `User[new_password]`.

См. [[../conventions/form-models]].

## Тема

CSS-классы `theme-light` / `theme-dark` на `<body>`. Применяется в `web/js/settings.js::applyTheme()`.

## Валюта

BYN (база), USD, EUR, RUB. Конвертация через [[../services/CurrencyService]].

## См. также

- [[../auth/index]]
- [[../conventions/form-models]]
