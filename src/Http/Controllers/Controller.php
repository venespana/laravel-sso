<?php

namespace Venespana\Sso\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $forceJson = false;

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
}
