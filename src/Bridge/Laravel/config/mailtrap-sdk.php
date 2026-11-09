<?php

return [
    'mailtrap-sdk' => [
        'host' => env('MAILTRAP_HOST', 'send.api.mailtrap.io'),
        'apiKey' => env('MAILTRAP_API_KEY'),
        'sandboxId' => env('MAILTRAP_SANDBOX_ID'),
        'inboxId' => env('MAILTRAP_INBOX_ID'), // deprecated, use MAILTRAP_SANDBOX_ID
    ],
];
