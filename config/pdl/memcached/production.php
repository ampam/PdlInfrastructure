<?php

return [
    'enabled' => true,
    'persistentId' => 'productionMain',
    'options' => [
        Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
        //Memcached::OPT_SERIALIZER => Memcached::SERIALIZER_JSON
    ],
    'servers' => [
        'server-name-1' => [
            'port' => 11211
        ],
        'server-name-2' => [
            'port' => 11211
        ],
    ]
];

