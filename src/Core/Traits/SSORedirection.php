<?php

namespace Venespana\Sso\Core\Traits;

use Venespana\Sso\Core\AuthSystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

trait SSORedirection
{
    public function redirectTo(string $returnUrl = null)
    {
        if (AuthSystem::isBroker()) {
            $url = $this->redirectBroker($returnUrl);
        }

        if ($url) {
            throw new HttpResponseException(redirect($url)->with('status', 307)
                ->with('message', "You're redirected to {$returnUrl}"));
        }
    }

    protected function redirectBroker(string $returnUrl = null): ?string
    {
        $user = Auth::user();
        $url = null;
        $currUrl = url()->full();
        $loginUrl = AuthSystem::loginUrl();

        if (!$user && $currUrl !== $loginUrl) {
            $data = http_build_query([
                'broker' => url()->full()
            ]);
            $url =  "{$loginUrl}?{$data}";
        } else {
            $url = Request::get('broker', null);
        }

        if (is_null($url)) {
            $url = $returnUrl;
        }

        return $url;
    }
}
