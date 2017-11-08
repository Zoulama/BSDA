<?php

namespace Provisioning\Providers;

use Illuminate\Support\ServiceProvider;
use Provisioning\Helpers\Auth\AuthUserClass;

class AuthUserServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('AuthUserClass', function ($app) {
            return new AuthUserClass();
        });
    }
}
