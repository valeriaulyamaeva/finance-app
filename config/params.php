<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'parserServiceUrl' => getenv('PARSER_SERVICE_URL') ?: 'http://parser:8000',
];
