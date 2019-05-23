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
    Route::match(array('GET', 'POST'), '', ['as' => 'server', 'uses' => 'Venespana\Sso\Http\Controllers\ServerController']);
});
