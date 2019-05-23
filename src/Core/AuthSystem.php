<?php

namespace Venespana\Sso\Core;

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

    public static function siBroker(): bool
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
}
