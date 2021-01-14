<?php

return [
    'server_timezone' => 'America/New_York',
    'logFile' => $_SERVER['DOCUMENT_ROOT'] . "/storage/logs/pdl.log",
    'debug' => [
        'logQueriesEnabled' => false
    ],
    'memcached' => include "memcached/config.php",
    'mysql' => include "db/mysql.php",
    'pdl' => [
        /**
         * Member names of the REQUEST or Request object excluded from PdlDecoder
         */
        'excludedRequestMembers' => [],

        /**
         * Type of object excluded from PdlDecoder, eg: BaseResponse or Response
         */
        'excludedClasses' => [],
        'projectNamespaces' => []
    ],
    //'rowFactoryImpl' => Com\Mh\Ds\Infrastructure\Data\RowFactory::class,
    //'DbOperationImpl' => Com\Mh\Ds\Infrastructure\Data\Db\MySql\DbConnectionImpl::class
    //'DbOperationImpl' => Com\Mh\Laravel\LaravelDbOperations::class
];
