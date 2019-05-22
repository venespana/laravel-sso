<?php

namespace Venespana\Sso;

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
            __DIR__.'/../configs/configs.php',
            'sso'
        );

        //     $this->loadViewsFrom(__DIR__.'/../resources/views', 'partners');

        //     Module::load();

        //     $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->loadRoutesFrom(__DIR__.'/../routes/sso.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register()
    {
        $this->commands([
            \Venespana\Sso\Console\Commands\Sso\Create::class
        ]);
    }
}
