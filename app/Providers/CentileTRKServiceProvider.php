<?php

namespace Provisioning\Providers;

use Illuminate\Support\ServiceProvider;
use Provisioning\Centile\Centile;
use Provisioning\Centile\SoapDriver;

class CentileTRKServiceProvider extends ServiceProvider
{
    protected $defer = true;

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
        $this->app->singleton('CentileTRK', function ($app) {
            return new Centile(new SoapDriver(
                config('centile.admin_username'),
                config('centile.admin_password'),
                SoapDriver::ROUTING_COMMUNITY_TYPE
            ));
        });
    }

    public function provides()
    {
        return ['CentileTRK'];
    }
}
