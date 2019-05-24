<?php

namespace Venespana\Sso\Core;

use Exception;
use Jasny\SSO\Broker;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Venespana\Sso\Core\AuthSystem;
use Jasny\SSO\NotAttachedException;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class SSOBroker extends Broker
{
    public function __construct()
    {
        $serverUrl = AuthSystem::serverUrl();
        $brokerId = AuthSystem::brokerId();
        $brokerSecret = AuthSystem::brokerScret();

        parent::__construct($serverUrl, $brokerId, $brokerSecret);
        $this->attach(true);
    }

    protected function request($method, $command, $data = null)
    {
        if (!$this->isAttached()) {
            throw new NotAttachedException('No token');
        }

        $url = $this->getRequestUrl($command, !$data || $method === 'POST' ? [] : $data);
        $requestType = strtolower($method);

        $request = new Request($method, $url, [
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->getSessionID()}"
        ]);

        if ($requestType === 'post' && !empty($data)) {
            $request->setBody($data);
        }

        $client = new Client();
        try {
            $response = $client->send($request);
        } catch (ClientException | ServerException $e) {
            $response = $e->getResponse();
            $status = $e->getCode();
            if ($status >= 500) {
                $this->clearToken();
                throw new Exception($e->getMessage());
            }
        }

        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $body = json_decode($body, true) ?? $body;

        $message = $body['message'];
        $data = $body['error'] ?? $body['data'] ?? null;

        if ($status === 403 || $status === 401) {
            $this->clearToken();
        }

        return $data;
    }

    /**
     * Attach our session to the user's session on the SSO server.
     *
     * @param string|true $returnUrl  The URL the client should be returned to after attaching
     */
    public function attach($returnUrl = null)
    {
        parent::attach($returnUrl);
    }

    /**
     * Get user information.
     *
     * @return object|null
     */
    public function getUserInfo()
    {
        if (!isset($this->userinfo)) {
            $this->userinfo = $this->request('GET', 'userInfo');
        }

        return $this->userinfo;
    }

    public function loginCurrentUser($returnUrl = '/home')
    {
        $user = $this->getUserInfo();
        if ($user) {
            Auth::loginUsingId($user['id']);
        }
        return redirect($returnUrl);
    }
}
