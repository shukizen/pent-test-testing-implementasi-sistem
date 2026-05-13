<?php

return [
    'secret' => env('NOCAPTCHA_SECRET'),
    'sitekey' => env('NOCAPTCHA_SITEKEY'),
    'options' => [
        'timeout' => 30,
        'verify' => false, // ✅ FIX: Bypass SSL verification untuk local environment Windows
    ],
];
