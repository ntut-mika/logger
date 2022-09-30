<?php

return [
    'hiddens' => [
        'authorization',
        'php-auth-pw',
        'password',
        'password_confirmation',
    ],
    'content_limit' => 64, // KB
    'ignore_models' => [
        \Mika\Logger\Models\Log::class
    ]
];
