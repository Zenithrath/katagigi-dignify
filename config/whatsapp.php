<?php

return [
    // Driver 'log' = catat + anggap terkirim (aman untuk dev/uji).
    // Driver 'cloud' = WhatsApp Cloud API (butuh token + nomor terdaftar).
    'enabled' => env('WA_ENABLED', true),
    'driver' => env('WA_DRIVER', 'log'),
    'base_url' => env('WA_BASE_URL', 'https://graph.facebook.com/v21.0'),
    'phone_number_id' => env('WA_PHONE_NUMBER_ID', ''),
    'access_token' => env('WA_ACCESS_TOKEN', ''),
    'clinic_name' => env('WA_CLINIC_NAME', 'Klinik Kata Gigi'),
];
