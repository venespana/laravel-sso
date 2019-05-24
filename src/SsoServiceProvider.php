<?php

namespace Venespana\Sso;

use Venespana\Sso\Core\AuthSystem;
use Illuminate\Support\ServiceProvider;

class SsoServiceProvider extends ServiceProvider
{
    /**
     * boot the service provider
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../configs/configs.php',
            'auth_system'
        );

        $this->publishes([
            __DIR__ . '/../configs/configs.php' => config_path('auth_system.php')
        ], 'auth_system');

        if (AuthSystem::isServer()) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/sso.php');
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->commands([
                \Venespana\Sso\Console\Commands\Sso\Create::class
            ]);
        }
    }

    public function register()
    { }
}
