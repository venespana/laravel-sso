<?php

namespace Venespana\Sso\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $forceJson = false;

    const REDIRECT = 'redirect';
    const RESPONSE = 'response';

    const REDIRECT_TO = 'to';
    const REDIRECT_ROUTE = 'route';

    public function response(string $message, $data = null, int $statusCode = 200)
    {
        $statusCode = $statusCode != 0 ? $statusCode : 500;
        $response = response();

        if (\Request::isJson() || $this->forceJson) {
            $result = $statusCode < 300 ? 'data' : 'error';
            $response = $response->json([
                'message' => $message,
                $result => $data
            ], $statusCode);
        } elseif ($statusCode < 300) {
            $data = $data ?? [];
            $response = $response->view($message, $data, $statusCode);
        } else {
            abort($statusCode, $message);
        }

        return $response;
    }

    public function redirect(string $url, string $type = 'to', $data = null, int $statusCode = 200)
    {
        $statusCode = $statusCode != 0 ? $statusCode : 500;

        if ($statusCode < 500) {
            $data = $data ?? [];

            if (static::REDIRECT_TO === $type) {
                $response = Redirect::to($url)->with('status', $statusCode)->with('message', $data);
            } else {
                $response = redirect()->{$type}($url, $data, $statusCode);
            }
        } else {
            abort($statusCode, $message);
        }

        return $response;
    }
}
