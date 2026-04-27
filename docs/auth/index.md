# Авторизация

Vale использует **Keycloak** как backend-сервис для логина и регистрации. Формы остаются на нашей стороне, Keycloak проверяет/создаёт пользователей через REST API.

## Документы

- [[keycloak]] — настройка Keycloak (docker, realm)
- [[login-flow]] — flow логина
- [[registration-flow]] — flow регистрации

## Ключевая идея

```
[Наша форма логина] → POST /token (Keycloak API)
                    → если ок, найти/создать локального user
                    → Yii::login() для сессии
```

Пароли хранятся **только в Keycloak**. Локальный `password_hash` — заглушка.

## См. также

- [[../services/KeycloakService]]
- [[../modules/settings]]
