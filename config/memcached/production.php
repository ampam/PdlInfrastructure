<?php

return [
    'enabled' => true,
    'persistentId' => 'productionMain',
    'options' => [
        Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
        //Memcached::OPT_SERIALIZER => Memcached::SERIALIZER_JSON
    ],
    'servers' => [
        'vw1' => [
            'port' => 11211
        ],
        'vw2' => [
            'port' => 11211
        ],
    ]
];

