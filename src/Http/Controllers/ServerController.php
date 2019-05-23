<?php

namespace Venespana\Sso\Http\Controllers;

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

        if (!is_null($method) && method_exists($sso, $method)) {
            if ($user = $sso->{$method}()) {
                $statusCode = 200;
                $data = $user;
            } else {
                $statusCode = 401;
                $message = 'Unauthorized';
            }
        } elseif (is_null($method)) {
            $message = 'Method must not be null';
        }

        return $this->response($message, $data, $statusCode);
    }
}
