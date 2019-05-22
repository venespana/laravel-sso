<?php

/*
|--------------------------------------------------------------------------
| SSO Routes
|--------------------------------------------------------------------------
|
*/

Route::group([
    'middleware' => 'web',
    'as' => 'sso.',
    'prefix' => 'sso'
], function () {
    Route::get('broker', ['as' => 'broker', 'uses' => 'Venespana\Sso\Http\Controllers\BrokerController@token']);
    Route::get('broker/{token}', ['as' => 'broker.token', 'uses' => 'Venespana\Sso\Http\Controllers\BrokerController@show']);
});
