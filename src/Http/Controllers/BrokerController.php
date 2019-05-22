<?php

namespace Venespana\Sso\Http\Controllers;

use Illuminate\Support\Str;
use Venespana\Sso\Models\Broker;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class BrokerController extends Controller
{
    public function token()
    {
        $hash = Request::get('hash');
        $secret = Request::get('secret');

        $data = ['error' => 'Invalid broker'];
        $status = 402;

        if (! is_null($hash) && ! is_null($secret) && Broker::where('hash', $hash)->where('secret', $secret)->exists()) {
            $token =  Str::random(64);

            Cache::put(config('sso.cache_prefix') . $token, $hash, 5);


            $status = 200;
            $data = [
                'token' => $token
            ];
        }

        return Response::json($data, $status);
    }

    public function show(string $token)
    {
        return Cache::get(config('sso.cache_prefix') . $token);
    }
}
