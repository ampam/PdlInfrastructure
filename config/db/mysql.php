<?php

return [
    'user_name' => 'root',
    'password' => '',
    'pconnect' => true,
    'db_name' => 'my_db',
    'autoIncrementStep' => 4,
    'charSet' => 'utf8',
    'dateTimeFormat' => [
        'native' => 'YYYY-MM-DD HH:MM:SS',
        'php24' => 'Y-m-d H:i:s',
        'phpGrid24' => 'm/d/Y H:i:s',
        'php' => 'Y-m-d h:i:s a'
    ],
    'masters' => [
        [
            'server' => 'localhost',
            'port' => 3306
        ]
    ],
    'slaves' => [
        [
            'server' => 'localhost',
            'port' => 3306
        ]
    ]


];
