<?php


return [
    'server_timezone' => 'America/New_York',
    'logFile' => $_SERVER['DOCUMENT_ROOT'] . "/storage/logs/pdl.log",
    'debug' => [
        'logQueriesEnabled' => false
    ],
    'memcached' => [
        'enabled' => true,
        'persistentId' => 'appMain',
        'options' => [
            /** Same as Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT */
            9 => 1,

            /** Same as Memcached::OPT_SERIALIZER => Memcached::SERIALIZER_JSON,*/
            //-1003 => 3
        ],
        'servers' => [
            'localhost' =>  [
                'port' => 11211
            ],
        ]
    ],
    'mysql' => [
        'user_name' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'pconnect' => true,
        'db_name' => env('DB_DATABASE', 'my_db'),
        'autoIncrementStep' => 1,
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


    ],
    'pdl' => [
        /**
         * Member names of the REQUEST or Request object excluded from PdlDecoder
         */
        'excludedRequestMembers' => [],

        /**
         * Type of object excluded from PdlDecoder, eg: BaseResponse or Response
         */
        'excludedClasses' => [],

        /**
         * Namespaces where your PDL generated classes are located
         */
        'projectNamespaces' => []
    ]
];
