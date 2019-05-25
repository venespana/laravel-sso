<?php

namespace Venespana\Sso\Core;

use Jasny\SSO\Server;
use Jasny\ValidationResult;
use Venespana\Sso\Models\Broker;
use Venespana\Sso\Core\AuthSystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Venespana\Sso\Http\Controllers\Controller;

class SSOServer extends Server
{
    /**
     * Authenticate using user credentials
     *
     * @param string $username
     * @param string $password
     * @return \Jasny\ValidationResult
     */
    protected function authenticate($username, $password): ValidationResult
    {
        if (!isset($username)) {
            return ValidationResult::error("username isn't set");
        }
        if (!isset($password)) {
            return ValidationResult::error("password isn't set");
        }
        if (Auth::attempt([AuthSystem::username() => $username, 'password' => $password])) {
            return ValidationResult::success();
        }
        return ValidationResult::error("can't find user");
    }

    /**
     * Get the API secret of a broker and other info
     *
     * @param string $brokerId
     * @return array
     */
    protected function getBrokerInfo($brokerId): ?array
    {
        $broker = Broker::where('hash', $brokerId)->first();
        if (!is_null($broker)) {
            $broker = $broker->toArray();
        }
        return $broker;
    }

    /**
     * Output on a successful attach
     *
     * @return array ['type' => string, 'data' => array|string, 'message' => string, 'status' => int]
     */
    protected function outputAttachSuccess(): array
    {
        $type = 'response';
        $status = 500;
        $message = '';
        $data = [];

        if ($this->returnType === 'image') {
            $this->outputImage();
        } elseif ($this->returnType === 'json') {
            header('Content-type: application/json; charset=UTF-8');
            echo json_encode(['success' => 'attached']);
        } elseif ($this->returnType === 'jsonp') {
            $data = json_encode(['success' => 'attached']);
            echo $_REQUEST['callback'] . "($data, 200);";
        } elseif ($this->returnType === 'redirect') {
            $status = 307;
            $type = Controller::REDIRECT;
            $data = Request::get('return_url');
            $message = "You are being redirected to {$data}";
        }

        return compact('type', 'data', 'message', 'status');
    }

    /**
     * Attach our session to the user's session on the SSO server.
     *
     * @param string|true $returnUrl  The URL the client should be returned to after attaching
     */
    public function attach($returnUrl = null)
    {
        $this->detectReturnType();

        if (empty($_REQUEST['broker'])) {
            return $this->fail("No broker specified", 400);
        }
        if (empty($_REQUEST['token'])) {
            return $this->fail("No token specified", 400);
        }

        if (!$this->returnType) {
            return $this->fail("No return url specified", 400);
        }

        $checksum = $this->generateAttachChecksum($_REQUEST['broker'], $_REQUEST['token']);

        if (empty($_REQUEST['checksum']) || $checksum != $_REQUEST['checksum']) {
            return $this->fail("Invalid checksum", 400);
        }

        $this->startUserSession();
        $sid = $this->generateSessionId($_REQUEST['broker'], $_REQUEST['token']);

        $this->cache->set($sid, $this->getSessionData('id'));
        return $this->outputAttachSuccess();
    }

    /**
     * Authenticate
     */
    public function login()
    {
        $this->startBrokerSession();

        $username = Request::get(AuthSystem::username(), '');
        $password = Request::get('password', '');

        if (empty($username)) {
            $this->fail("No username specified", 400);
        }
        if (empty($password)) {
            $this->fail("No password specified", 400);
        }

        $validation = $this->authenticate($username, $password);

        if ($validation->failed()) {
            return $this->fail($validation->getError(), 400);
        }

        $this->setSessionData('sso_user', $username);
        return $this->userInfo();
    }

    /**
     * Get the information about a user
     *
     * @param string $username
     * @return array|object
     */
    protected function getUserInfo($username)
    {
        $user = AuthSystem::model()::where(AuthSystem::username(), $username)->first();
        $response = [];
        foreach (AuthSystem::responseFields() as $key => $value) {
            $field = $user->{$value} ?? null;
            if (is_null($field)) {
                continue;
            }
            $response[$key] = $field;
        }
        return count($response) > 0 ? $response : $user;
    }

    /**
     * Return the user information
     *
     * @return array|object|null
     */
    public function userInfo()
    {
        $this->startBrokerSession();
        $user = null;

        $username = $this->getSessionData('sso_user');

        if ($username) {
            $user = $this->getUserInfo($username);
            if (!$user) {
                return $this->fail("User not found", 500); // Shouldn't happen
            }
        }

        return $user;
    }
}
