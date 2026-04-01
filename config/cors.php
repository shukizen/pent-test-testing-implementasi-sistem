<?php

return [
    // VULNERABLE A05: Overly permissive CORS configuration
    'paths' => ['api/*', '*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // VULNERABLE: Allows any origin
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // VULNERABLE: credentials with wildcard origin
];
