<?php

return [
    'connection' => env('PBI_ACCESS_CONNECTION', 'pbi'),

    'admin' => [
        'username' => env('PBI_ADMIN_USERNAME'),
        'password_hash' => env('PBI_ADMIN_PASSWORD_HASH'),
    ],
];
