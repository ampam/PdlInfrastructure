<?php

return [
    'enabled' => true,
    'persistentId' => 'stagingMain',
    'options' => [
        Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
        //Memcached::OPT_SERIALIZER => Memcached::SERIALIZER_JSON
    ],
    'servers' => [
        'localhost' =>  [
            'port' => 11211
        ],
    ]
];

