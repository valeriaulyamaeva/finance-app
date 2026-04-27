# Login Flow

```
[User] вводит email + password
   ↓
[SiteController::actionLogin] (POST /login)
   ↓
[KeycloakService::authenticate()]
   ↓ POST http://keycloak:8080/realms/vale/protocol/openid-connect/token
   ↓ grant_type=password
[Keycloak проверяет credentials]
   ↓ возвращает access_token (JWT)
[KeycloakService] декодирует JWT → email, username
   ↓
[SiteController] User::findOne(email)
   ↓ если нет — создаёт локального + дефолтные категории
   ↓ если есть — синхронизирует username
[Yii::$app->user->login($user, 30 days)]
   ↓
[Redirect /analytics]
```

## Важно

- Это **не** OIDC redirect flow. Формы наши, Keycloak только проверяет credentials через REST API.
- Пароли хранятся **только в Keycloak**. В локальной БД `password_hash` — заглушка.
- При первом логине через Keycloak локальный юзер создаётся + дефолтные категории через [[../services/CategoryService]]
- При повторных логинах username синхронизируется из JWT

## Файлы

- `controllers/SiteController.php::actionLogin()`
- `services/KeycloakService.php::authenticate()`
- `views/site/login.php`
- `models/User.php` (scenario `login`)

## См. также

- [[registration-flow]]
- [[../services/KeycloakService]]
- [[keycloak]]
