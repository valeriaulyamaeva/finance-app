<?php

declare(strict_types=1);

namespace app\services;

use Exception;
use Yii;

readonly class KeycloakService
{
    private string $baseUrl;
    private string $realm;
    private string $clientId;
    private string $adminUser;
    private string $adminPassword;

    public function __construct()
    {
        $this->baseUrl = Yii::$app->params['keycloakInternalUrl'] ?? 'http://keycloak:8080';
        $this->realm = Yii::$app->params['keycloakRealm'] ?? 'vale';
        $this->clientId = Yii::$app->params['keycloakClientId'] ?? 'vale-app';
        $this->adminUser = Yii::$app->params['keycloakAdminUser'] ?? 'admin';
        $this->adminPassword = Yii::$app->params['keycloakAdminPassword'] ?? 'admin';
    }

    /**
     * Authenticate user via Keycloak token endpoint (Resource Owner Password Grant).
     * Returns user info array on success, null on failure.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $url = "{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token";

        $response = $this->post($url, [
            'grant_type' => 'password',
            'client_id' => $this->clientId,
            'username' => $email,
            'password' => $password,
        ]);

        if (!$response || isset($response['error'])) {
            return null;
        }

        // Decode the access token to get user info
        $tokenParts = explode('.', $response['access_token']);
        if (count($tokenParts) < 2) {
            return null;
        }

        $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);

        return [
            'sub' => $payload['sub'] ?? null,
            'email' => $payload['email'] ?? $email,
            'name' => $payload['preferred_username'] ?? $payload['name'] ?? $email,
        ];
    }

    /**
     * Register a new user in Keycloak via Admin REST API.
     * Returns true on success, throws Exception on failure.
     */
    public function register(string $email, string $password, string $username): bool
    {
        $adminToken = $this->getAdminToken();
        if (!$adminToken) {
            throw new Exception('Не удалось подключиться к серверу авторизации');
        }

        $url = "{$this->baseUrl}/admin/realms/{$this->realm}/users";

        $userData = [
            'username' => $email,
            'email' => $email,
            'firstName' => $username,
            'lastName' => $username,
            'enabled' => true,
            'emailVerified' => true,
            'credentials' => [
                [
                    'type' => 'password',
                    'value' => $password,
                    'temporary' => false,
                ],
            ],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($userData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $adminToken,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            return true;
        }

        if ($httpCode === 409) {
            throw new Exception('Пользователь с таким email уже существует');
        }

        $body = json_decode($response, true);
        $message = $body['errorMessage'] ?? $body['error'] ?? "HTTP $httpCode";
        throw new Exception("Ошибка регистрации: $message");
    }

    /**
     * Get admin access token for Keycloak Admin REST API.
     */
    private function getAdminToken(): ?string
    {
        $url = "{$this->baseUrl}/realms/master/protocol/openid-connect/token";

        $response = $this->post($url, [
            'grant_type' => 'password',
            'client_id' => 'admin-cli',
            'username' => $this->adminUser,
            'password' => $this->adminPassword,
        ]);

        return $response['access_token'] ?? null;
    }

    private function post(string $url, array $fields): ?array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            Yii::error("Keycloak request failed: $error");
            return null;
        }

        return json_decode($response, true);
    }
}
