# Keycloak

Сервер аутентификации (Keycloak 26 + PostgreSQL).

## Docker

```yaml
keycloak:
  image: quay.io/keycloak/keycloak:26.0
  command: start-dev --import-realm
  ports: ["8180:8080"]
  environment:
    KC_DB: postgres
    KC_DB_URL: jdbc:postgresql://keycloak_db:5432/keycloak
    KEYCLOAK_ADMIN: admin
    KEYCLOAK_ADMIN_PASSWORD: admin
  volumes:
    - ./docker/keycloak/realm-export.json:/opt/keycloak/data/import/realm-export.json
```

## Realm: `vale`

`docker/keycloak/realm-export.json`:
- `registrationAllowed: true`
- `loginWithEmailAllowed: true`
- `verifyEmail: false`
- `VERIFY_PROFILE: disabled` (важно — иначе password grant возвращает "Account is not fully set up")

## Client: `vale-app`

- `publicClient: true`
- `directAccessGrantsEnabled: true` (для Resource Owner Password Grant)
- `redirectUris: ["http://localhost:8080/*"]`

## Admin Console

http://localhost:8180 (admin/admin) — управление realms/users/clients.

## Внутренний URL

PHP-контейнер обращается через Docker-сеть: `http://keycloak:8080`. Браузер использует `http://localhost:8180`.

## См. также

- [[../services/KeycloakService]]
- [[../infrastructure/docker-services]]
