<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'parserServiceUrl' => getenv('PARSER_SERVICE_URL') ?: 'http://parser:8000',
    'keycloakInternalUrl' => getenv('KEYCLOAK_INTERNAL_URL') ?: 'http://keycloak:8080',
    'keycloakRealm' => getenv('KEYCLOAK_REALM') ?: 'vale',
    'keycloakClientId' => getenv('KEYCLOAK_CLIENT_ID') ?: 'vale-app',
    'keycloakAdminUser' => getenv('KEYCLOAK_ADMIN_USER') ?: 'admin',
    'keycloakAdminPassword' => getenv('KEYCLOAK_ADMIN_PASSWORD') ?: 'admin',
];
