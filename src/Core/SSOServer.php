<?php

namespace Venespana\Sso\Core;

use Jasny\SSO\Server;
use Jasny\ValidationResult;
use Venespana\Sso\Models\Broker;

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
        if (Auth::attempt(['email' => $username, 'password' => $password])) {
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
     * Get the information about a user
     *
     * @param string $username
     * @return array|object
     */
    protected function getUserInfo($username)
    {
        // $user = User::where('email', $username)->first();
        // return $user ? $user : null;
    }

    /**
     * Return the user information
     */
    public function userInfo(): ?array
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
