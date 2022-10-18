<?php

return [
    // which data should be hide
    'hiddens' => [
        'authorization',
        'php-auth-pw',
        'password',
        'password_confirmation',
    ],
    'content_limit' => 64, // KB

    // which model should be ingore
    // keep \Mika\Logger\Models\Log::class in list
    'ignore_models' => [
        \Mika\Logger\Models\Log::class
    ]
];
