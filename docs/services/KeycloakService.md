# KeycloakService

REST-клиент Keycloak Admin API. Используется для логина и регистрации.

**Файл**: `services/KeycloakService.php`

## Конфиг

```php
'keycloakInternalUrl' => 'http://keycloak:8080',  // Docker-internal
'keycloakRealm' => 'vale',
'keycloakClientId' => 'vale-app',
'keycloakAdminUser' => 'admin',
'keycloakAdminPassword' => 'admin',
```

## Методы

### `authenticate(string $email, string $password): ?array`

POST `/realms/vale/protocol/openid-connect/token` (grant_type=password).
Возвращает `['sub', 'email', 'name']` из JWT или `null`.

### `register(string $email, string $password, string $username): bool`

1. Получает admin token (`getAdminToken()` через `admin-cli` client)
2. POST `/admin/realms/vale/users` с данными
3. Возвращает true / бросает Exception (HTTP 409 = email уже существует)

### `getAdminToken(): ?string`

Внутренний — получает токен через master realm + admin-cli.

## Важно

- Использует **Resource Owner Password Grant** — нужен `directAccessGrantsEnabled: true` у клиента
- `lastName` тоже передаётся при регистрации (иначе VERIFY_PROFILE блокирует password grant в Keycloak 26)
- VERIFY_PROFILE отключён в realm-export.json

## См. также

- [[../auth/keycloak]]
- [[../auth/login-flow]]
- [[../auth/registration-flow]]
