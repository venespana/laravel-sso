<?php

namespace Venespana\Sso\Core;

use Exception;
use Jasny\SSO\Broker;
use GuzzleHttp\Client;
use Venespana\Sso\Core\AuthSystem;
use Jasny\SSO\NotAttachedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Venespana\Sso\Core\Traits\SSORedirection;
use Illuminate\Http\Exceptions\HttpResponseException;

class SSOBroker extends Broker
{
    use SSORedirection;

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

        $httpData = [
            'base_uri' => $url,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$this->getSessionID()}"
            ]
        ];
        if ($requestType === 'post' && !empty($data)) {
            $httpData['json'] = $data;
        }

        $client = new Client($httpData);
        try {
            $response = $client->{$requestType}('');
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

        $message = $body['error'] ?? $body['message'] ?? $body['data'] ?? null;
        $data = $body['error'] ?? $body['data'] ?? null;

        if ($status === 403 || $status === 401) {
            $this->clearToken();
        } elseif ($status >= 400) {
            throw new Exception(is_string($message) ? $message : 'Undefined error', $status);
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
        if ($this->isAttached()) {
            return;
        }

        if ($returnUrl === true) {
            $protocol = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $returnUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        $params = ['return_url' => $returnUrl];
        $url = $this->getAttachUrl($params);

        throw new HttpResponseException(redirect($url)
            ->with('status', 307)
            ->with('message', "You're redirected to {$url}"));
    }

    /**
     * Log the client in at the SSO server.
     *
     * Only brokers marked trused can collect and send the user's credentials. Other brokers should omit $username and
     * $password.
     *
     * @param string $username
     * @param string $password
     * @return array  user info
     * @throws Exception if login fails eg due to incorrect credentials
     */
    public function login($username = null, $password = null)
    {
        if (!isset($username)) {
            $username = Request::get(AuthSystem::username());
        }
        if (!isset($password)) {
            $password = Request::get('password');
        }

        $result = $this->request('POST', 'login', [
            AuthSystem::username() => $username,
            'password' => $password
        ]);
        $this->userinfo = $result;

        return $this->userinfo;
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

        if (is_array($this->userinfo) && count($this->userinfo) > 0) {
            foreach (AuthSystem::responseFields() as $key => $value) {
                $field = $this->userinfo[$value] ?? null;
                $response[$key] = $field;
            }

            $this->userinfo = $response;
        }

        return is_array($this->userinfo) ? $this->userinfo : null;
    }

    /**
     * Redirect to selected url when user is login and return to
     * url seted in configs "auth_system.login.url" if unatenticted
     *
     * @param string $returnUrl
     * @param boolean $keepInBroker
     * @return void
     */
    public function loginCurrentUser($returnUrl = '')
    {
        $user = $this->getUserInfo();
        $this->authUser($user);

        $this->redirectTo($returnUrl);
    }

    protected function authUser(?array $user): bool
    {
        $result = false;
        if (Auth::user()) {
            $result = true;
        }

        if (is_array($user) && !$result) {
            $data = Auth::loginUsingId($user[AuthSystem::userIdField()]);
            if (!$data) {
                AuthSystem::model()::create($user);
                $result = $this->authUser($user);
            } else {
                $result = true;
            }
        }

        return $result;
    }
}
