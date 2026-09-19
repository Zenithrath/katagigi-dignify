<?php

return [
    // Jangan klaim "terintegrasi" sebelum bridging production lolos (PRD §1).
    'enabled' => env('SATUSEHAT_ENABLED', false),
    'env' => env('SATUSEHAT_ENV', 'sandbox'),
    'base_url' => env('SATUSEHAT_BASE_URL', 'https://api-satusehat-dev.dto.kemkes.go.id'),
    'auth_url' => env('SATUSEHAT_AUTH_URL', 'https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1'),
    'org_id' => env('SATUSEHAT_ORG_ID', ''),
    'client_id' => env('SATUSEHAT_CLIENT_ID', ''),
    'client_secret' => env('SATUSEHAT_CLIENT_SECRET', ''),
];
