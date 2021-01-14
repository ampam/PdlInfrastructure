<?php

return [
    'user_name' => env('DB_USERNAME','root'),
    'password' => env('DB_PASSWORD',''),
    'pconnect' => true,
    'db_name' => env('DB_DATABASE', 'my_db'),
    'autoIncrementStep' => 4,
    'charSet' => env('DB_CHARSET', 'utf8mb4'),
    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'masters' => [
        [
            'server' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306')
        ]
    ],
    'slaves' => [
        [
            'server' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306')
        ]
    ]


];
