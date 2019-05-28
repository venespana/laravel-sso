<?php

namespace Venespana\Sso\Core;

use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Auth\User;

class AuthSystem
{
    public const SERVER = 'server';
    public const HYBRID = 'hybrid';
    public const BROKER = 'broker';
    public const APP = 'app';

    public static function is(string $type): bool
    {
        return config('auth_system.type', '') === $type;
    }

    public static function isServer(): bool
    {
        return static::is(static::SERVER) || static::is(static::HYBRID);
    }

    public static function isBroker(): bool
    {
        return static::is(static::BROKER) || static::is(static::HYBRID);
    }

    public static function isApp(): bool
    {
        return static::is(static::APP);
    }

    public static function serverUrl(): string
    {
        $url = '';
        if (static::isServer()) {
            $url = route('sso.server');
        } else {
            $url = config('auth_system.broker_data.server', $url);
        }

        return $url;
    }

    public static function brokerId(): string
    {
        $id = config('auth_system.broker_data.hash', '');
        return $id;
    }

    public static function brokerScret(): string
    {
        $secret = config('auth_system.broker_data.secret', '');
        return $secret;
    }

    public static function username(): string
    {
        return config('auth_system.login.username', 'email');
    }

    public static function model(): string
    {
        return config('auth_system.login.model', User::class);
    }

    public static function responseFields(): array
    {
        return config('auth_system.login.response_fields', []);
    }

    public static function loginUrl(): string
    {
        $url = config('auth_system.login.url', '');

        if (is_null($url)) {
            $url = URL::to('/');
        }
        return $url;
    }

    public static function userIdField(): string
    {
        $idField = config('auth_system.login.user_id_field', 'id');
        return $idField;
    }

    public static function brokerRedirUrl(): ?string
    {
        $url = \Request::get('broker', null);
        return $url;
    }
}
