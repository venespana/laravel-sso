<?php

return [
    /**
     * The type must be one of this types
     *
     * 'server' => Is a sso server system
     * 'hybrid' => Is a sso server and broker
     * 'broker' => App with permission to login in sso
     * 'app' => stand alone app with login system
     */
    'type' => 'hybrid',

    'broker_table' => 'brokers',
    'cache_prefix' => 'venespana_sso.',

    'broker_data' => [
        'server' => env('SSO_SERVER', null),
        'hash' => env('SSO_BROKER_HASH', null),
        'secret' => env('SSO_BROKER_SECRET', null)
    ]
];
