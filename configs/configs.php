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


    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the SSO server.
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if this page is working as SSO server.
     |
     */

    'broker_table' => 'brokers',
    'cache_prefix' => 'venespana_sso.',

    'login' => [
        'model' => \App\User::class,
        'username' => 'username',
        'user_id_field' => 'id',

        // Url to login system
        'url' => env('SSO_SERVER_LOGIN', null),

        // Logged in user fields sent to brokers.
        'response_fields' => [
            'id' => 'id',
            'username' => 'name'
        ]
    ],

    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the SSO broker.
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if this page is working as SSO broker.
     |
     */

    'broker_data' => [
        'server' => env('SSO_SERVER', null),
        'hash' => env('SSO_BROKER_HASH', null),
        'secret' => env('SSO_BROKER_SECRET', null)
    ],
];