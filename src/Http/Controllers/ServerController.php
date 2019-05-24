<?php

namespace Venespana\Sso\Http\Controllers;

use Illuminate\Support\Arr;
use Venespana\Sso\Core\SSOServer;
use Illuminate\Support\Facades\Request;
use Venespana\Sso\Http\Controllers\Controller;

class ServerController extends Controller
{
    protected $forceJson = true;

    public function __invoke(SSOServer $sso)
    {
        $method = Request::get('method', Request::get('command', null));
        $message = "Method {$method} not allowed";
        $data = [];
        $statusCode = 404;
        $type = 'response';

        if (!is_null($method) && method_exists($sso, $method)) {
            if ($response = $sso->{$method}()) {
                $statusCode = Arr::get($response, 'status', 200);
                $data = Arr::get($response, 'data', $response);
                $type = Arr::get($response, 'type', $type);
            } else {
                $statusCode = 401;
                $message = 'Unauthorized';
                $data = $response;
            }
        } elseif (is_null($method)) {
            $message = 'Method must not be null';
        }

        if ($type === static::REDIRECT) {
            $result = $this->redirect($data, static::REDIRECT_TO, $message, $statusCode);
        } else {
            $result = $this->response($message, $data, $statusCode);
        }
        return $result;
    }
}
