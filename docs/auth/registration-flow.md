# Registration Flow

```
[User] заполняет форму (username, email, password, password_repeat)
   ↓
[SiteController::actionRegister] (POST /register)
   ↓
[Validate locally] — email unique, password >=8, repeat match
   ↓ (если ошибка — render обратно, в Keycloak ничего не уйдёт)
[KeycloakService::register()]
   ↓ getAdminToken() через admin-cli
   ↓ POST http://keycloak:8080/admin/realms/vale/users
   ↓ {username: email, email, firstName, lastName, password, enabled, emailVerified}
[Keycloak создаёт user]
   ↓
[Локально создаётся User]
   ↓ setPassword (заглушка), generateAuthKey, save
   ↓ createDefaultCategories
[Yii::login() — авто-логин после регистрации]
   ↓
[Redirect /analytics]
```

## Важно: lastName

Без `lastName` Keycloak 26 блокирует password grant (`Account is not fully set up`) даже когда VERIFY_PROFILE отключён. Передаём `lastName: $username`.

## Файлы

- `controllers/SiteController.php::actionRegister()`
- `services/KeycloakService.php::register()`
- `views/site/register.php`
- `models/User.php` (scenario `create`)

## См. также

- [[login-flow]]
- [[keycloak]]
